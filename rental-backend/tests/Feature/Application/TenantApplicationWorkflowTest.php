<?php

namespace Tests\Feature\Application;

use App\Models\Blacklist;
use App\Models\PropertyApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class TenantApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        Mail::fake();
    }

    private function applicationPayload(array $overrides = []): array
    {
        return array_merge([
            'message'          => 'I would love to rent this property.',
            'preferred_move_in' => now()->addMonth()->format('Y-m-d'),
            'adults'           => 2,
            'children'         => 0,
            'has_pets'         => false,
        ], $overrides);
    }

    private function mobileMoneyPayload(): array
    {
        return [
            'method'   => 'mobile_money',
            'provider' => 'mtn',
            'phone'    => '0966123456',
        ];
    }

    // ── Submit application ────────────────────────────────────────────────────

    public function test_tenant_can_apply_for_an_available_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.apply.store', $property), $this->applicationPayload())
            ->assertRedirect(route('tenant.applications.index'));

        $this->assertDatabaseHas('property_applications', [
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);
    }

    public function test_blacklisted_tenant_cannot_apply(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        Blacklist::create([
            'nrc_passport'   => '000000/00/0',
            'email'          => $tenant->email,
            'reason'         => 'Fraud',
            'type'           => 'fraud',
            'blacklisted_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($tenant)
            ->post(route('properties.apply.store', $property), $this->applicationPayload())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('property_applications', [
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
        ]);
    }

    public function test_tenant_cannot_apply_to_unavailable_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['availability_status' => 'rented']);
        $tenant   = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.apply.store', $property), $this->applicationPayload())
            ->assertSessionHas('error');
    }

    public function test_tenant_cannot_submit_duplicate_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        // First application
        PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($tenant)
            ->post(route('properties.apply.store', $property), $this->applicationPayload())
            ->assertRedirect(route('tenant.applications.index'));

        // Only one application should exist
        $this->assertDatabaseCount('property_applications', 1);
    }

    public function test_non_visible_property_blocks_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, [
            'is_visible_in_search' => false,
            'approval_status'      => 'pending',
        ]);
        $tenant = $this->makeTenant();

        $this->actingAs($tenant)
            ->post(route('properties.apply.store', $property), $this->applicationPayload())
            ->assertSessionHas('error');
    }

    // ── Landlord workflow ─────────────────────────────────────────────────────

    public function test_landlord_can_start_review_on_pending_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.property-applications.start-review', $application))
            ->assertRedirect();

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'under_review',
        ]);
    }

    public function test_landlord_cannot_start_review_on_application_for_another_landlords_property(): void
    {
        $landlord1 = $this->makeLandlord();
        $landlord2 = $this->makeLandlord();
        $property  = $this->makeProperty($landlord1);
        $tenant    = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($landlord2)
            ->post(route('landlord.property-applications.start-review', $application))
            ->assertForbidden();
    }

    public function test_landlord_can_request_payment_from_under_review_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'under_review',
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.property-applications.request-payment', $application), [
                'deadline_hours' => 48,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'payment_requested',
        ]);
    }

    public function test_landlord_cannot_request_payment_on_pending_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.property-applications.request-payment', $application), [
                'deadline_hours' => 24,
            ])
            ->assertSessionHas('error');
    }

    public function test_landlord_can_reject_an_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($landlord)
            ->post(route('landlord.property-applications.reject', $application), [
                'reason' => 'Does not meet criteria.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'rejected',
        ]);
    }

    // ── Tenant payment & cancellation ─────────────────────────────────────────

    public function test_tenant_can_pay_and_complete_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id'          => $property->id,
            'user_id'              => $tenant->id,
            'status'               => 'payment_requested',
            'payment_requested_at' => now(),
            'payment_deadline'     => now()->addHours(48),
        ]);

        $this->actingAs($tenant)
            ->post(route('properties.pay', [$property, $application]), $this->mobileMoneyPayload())
            ->assertRedirect(route('tenant.applications.index'));

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $tenant->id,
            'Type'    => 'MOBILE_MONEY',
            'Status'  => 'COMPLETED',
        ]);

        $this->assertDatabaseHas('properties', [
            'id'                  => $property->id,
            'availability_status' => 'rented',
        ]);
    }

    public function test_paying_tenant_causes_other_active_applications_to_be_rejected(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant1  = $this->makeTenant();
        $tenant2  = $this->makeTenant();

        $winningApp = PropertyApplication::create([
            'property_id'          => $property->id,
            'user_id'              => $tenant1->id,
            'status'               => 'payment_requested',
            'payment_requested_at' => now(),
            'payment_deadline'     => now()->addHours(48),
        ]);

        $losingApp = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant2->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($tenant1)
            ->post(route('properties.pay', [$property, $winningApp]), $this->mobileMoneyPayload());

        $this->assertDatabaseHas('property_applications', ['id' => $losingApp->id, 'status' => 'rejected']);
    }

    public function test_tenant_can_cancel_a_pending_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($tenant)
            ->delete(route('applications.cancel', $application))
            ->assertRedirect(route('tenant.applications.index'));

        $this->assertDatabaseHas('property_applications', [
            'id'     => $application->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_tenant_cannot_cancel_another_tenants_application(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $tenant1  = $this->makeTenant();
        $tenant2  = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id' => $property->id,
            'user_id'     => $tenant1->id,
            'status'      => 'pending',
        ]);

        $this->actingAs($tenant2)
            ->delete(route('applications.cancel', $application))
            ->assertForbidden();
    }

    public function test_cancelling_payment_requested_application_restores_property_availability(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['availability_status' => 'available']);
        $tenant   = $this->makeTenant();

        $application = PropertyApplication::create([
            'property_id'          => $property->id,
            'user_id'              => $tenant->id,
            'status'               => 'payment_requested',
            'payment_requested_at' => now(),
            'payment_deadline'     => now()->addHours(48),
        ]);

        $this->actingAs($tenant)
            ->delete(route('applications.cancel', $application));

        $this->assertDatabaseHas('properties', [
            'id'                  => $property->id,
            'availability_status' => 'available',
        ]);
    }
}
