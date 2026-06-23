<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created transaction (Payment API).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'phone' => 'nullable|string',
            'name' => 'required|string',
            'type' => 'nullable|string|max:4',
            'nrc' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $transactionId = 'TXN-' . strtoupper(bin2hex(random_bytes(6)));

        $transaction = \App\Models\Transaction::create([
            'UID' => (string) \Illuminate\Support\Str::uuid(),
            'RequestID' => (string) \Illuminate\Support\Str::uuid(),
            'TransactionID' => $transactionId,
            'UserID' => auth()->id(),
            'NRC' => $validated['nrc'] ?? null,
            'TransactionDate' => now()->toDateString(),
            'Amount' => $validated['amount'],
            'Name' => $validated['name'],
            'Type' => $validated['type'] ?? 'CARD',
            'Phone' => $validated['phone'] ?? null,
            'Status' => 'COMPLETED',
            'Data' => json_encode($validated['data'] ?? []),
            'Timestamp' => now(),
        ]);

        // If it's a rent payment linked to a property/application
        if (isset($validated['data']['application_id'])) {
            $application = \App\Models\PropertyApplication::with('property.landlord')
                ->find($validated['data']['application_id']);

            if ($application) {
                // Notify Landlord (Created in Phase 1)
                \Illuminate\Support\Facades\Mail::to($application->property->landlord->email)
                    ->send(new \App\Mail\PaymentReceivedLandlord(
                        $application->property->title,
                        $validated['amount'],
                        auth()->user()->name
                    ));
            }
        }

        return response()->json([
            'message' => 'Payment processed successfully',
            'transaction' => $transaction
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
