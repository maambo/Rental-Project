<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyApplication\StorePropertyApplicationRequest;
use App\Mail\ApplicationSubmittedTenant;
use App\Mail\ApplicationReceivedLandlord;
use App\Mail\PaymentReceivedLandlord;
use App\Models\Blacklist;
use App\Models\Property;
use App\Models\PropertyApplication;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PropertyApplicationController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function create(Property $property)
    {
        if (! Property::visibleInSearch()->where('id', $property->id)->exists()) {
            return back()->with('error', 'This property is not available.');
        }

        // Block if user already has an active application for this property
        $hasActive = PropertyApplication::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->active()
            ->exists();

        if ($hasActive) {
            return redirect()->route('tenant.applications.index')
                ->with('error', 'You already have an active application for this property.');
        }

        return Inertia::render('Properties/Apply', [
            'property' => $property->load(['province', 'district', 'town', 'images']),
        ]);
    }

    public function store(StorePropertyApplicationRequest $request, Property $property)
    {
        // Block if property is not visible/approved
        if (! Property::visibleInSearch()->where('id', $property->id)->exists()) {
            return back()->with('error', 'This property is not currently accepting applications.');
        }

        // Block blacklisted users
        $user = auth()->user();
        $isBlacklisted = Blacklist::where('email', $user->email)->exists();
        if ($isBlacklisted) {
            return back()->with('error', 'Your account is not permitted to submit applications.');
        }

        // Block if already rented/sold
        if ($property->availability_status !== 'available') {
            return back()->with('error', 'This property is no longer available.');
        }

        // One active application per tenant per property
        $hasActive = PropertyApplication::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->active()
            ->exists();

        if ($hasActive) {
            return redirect()->route('tenant.applications.index')
                ->with('error', 'You already have an active application for this property.');
        }

        $data = array_merge(
            $request->only(['message', 'preferred_move_in', 'additional_comments', 'applicant_terms']),
            ['user_id' => auth()->id(), 'property_id' => $property->id, 'status' => 'pending']
        );

        if ($property->isResidentialRent()) {
            $data = array_merge($data, $request->only(['adults', 'children', 'has_pets', 'pet_details']));
        }

        if ($property->isCommercial()) {
            $data = array_merge($data, $request->only(['intended_use', 'business_name']));
        }

        $application = PropertyApplication::create($data);
        $application->load(['property.province', 'property.district', 'property.town', 'property.landlord', 'user']);

        // Email tenant: confirmation
        Mail::to($application->user->email)->send(new ApplicationSubmittedTenant($application));

        // Email landlord: new application
        if ($application->property->landlord?->email) {
            Mail::to($application->property->landlord->email)->send(new ApplicationReceivedLandlord($application));
        }

        return redirect()->route('tenant.applications.index')
            ->with('success', 'Application submitted! You will be notified when the landlord responds.');
    }

    /** Tenant cancels their own application (only if still active). */
    public function cancel(PropertyApplication $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $application->isActive()) {
            return back()->with('error', 'This application can no longer be cancelled.');
        }

        $wasPaymentRequested = $application->status === 'payment_requested';

        $application->update([
            'status'           => 'cancelled',
            'rejection_reason' => 'Cancelled by applicant.',
        ]);

        // If the tenant had payment requested, restore the property to available
        // so the landlord can proceed with another applicant.
        if ($wasPaymentRequested) {
            $application->property->update([
                'availability_status'    => 'available',
                'availability_changed_at' => now(),
            ]);
        }

        return redirect()->route('tenant.applications.index')
            ->with('success', 'Application cancelled.');
    }

    /** Tenant makes payment — charges via the simulated payment method, then finalises the deal. */
    public function pay(Request $request, Property $property, PropertyApplication $application)
    {
        if ($application->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $application->isPaymentRequested()) {
            return back()->with('error', 'Payment is not currently requested for this application.');
        }

        if ($application->isPaymentExpired()) {
            return back()->with('error', 'The payment deadline has passed. This application has expired.');
        }

        // Load before transaction so relationships are available for emails
        $application->load(['property.landlord', 'user']);
        $property = $application->property;

        $this->paymentService->initiateRentPayment($application, $request->only([
            'method', 'provider', 'phone', 'card_number', 'card_expiry', 'card_cvv', 'cardholder_name',
        ]));

        DB::transaction(function () use ($application, $property) {
            $application->update([
                'status'                 => 'completed',
                'completed_at'           => now(),
                'tenant_agreed_terms_at' => now(),
            ]);

            $newStatus = $property->listing_type === 'sale' ? 'sold' : 'rented';
            $property->update([
                'availability_status'    => $newStatus,
                'availability_changed_at' => now(),
            ]);

            PropertyApplication::where('property_id', $property->id)
                ->where('id', '!=', $application->id)
                ->active()
                ->update([
                    'status'           => 'rejected',
                    'rejection_reason' => 'Property has been ' . $newStatus . ' to another applicant.',
                ]);
        });

        // Notify landlord that payment has been confirmed
        if ($property->landlord?->email) {
            Mail::to($property->landlord->email)->send(new PaymentReceivedLandlord(
                amount: $property->price,
                propertyName: $property->title,
                transactionId: $application->id,
                date: now()->format('d M Y, H:i'),
                tenantName: $application->user->name,
            ));
        }

        return redirect()->route('tenant.applications.index')
            ->with('success', 'Payment confirmed! The property has been secured for you.');
    }
}
