<?php

namespace Tests\Feature;

use App\Models\RentalHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class ChatRentalHistoryTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── ChatController ────────────────────────────────────────────────

    public function test_authenticated_user_can_view_chat_index(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('chat.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Chat/Index')
                 ->has('conversations')
             );
    }

    public function test_authenticated_user_can_view_chat_with_specific_user(): void
    {
        $tenant    = $this->makeTenant();
        $landlord  = $this->makeLandlord();

        $this->actingAs($tenant)
             ->get(route('chat.show', $landlord))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Chat/Index')
                 ->has('recipient')
             );
    }

    public function test_unauthenticated_user_cannot_access_chat(): void
    {
        $this->get(route('chat.index'))->assertRedirect(route('login'));
    }

    // ── RentalHistoryController ───────────────────────────────────────

    public function test_tenant_can_view_their_rental_history(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        RentalHistory::create([
            'property_id'  => $property->id,
            'tenant_id'    => $tenant->id,
            'landlord_id'  => $landlord->id,
            'monthly_rent' => 2500,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subMonth(),
        ]);

        $this->actingAs($tenant)
             ->get(route('rental-history.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('RentalHistory/Index')
                 ->has('history')
             );
    }

    public function test_landlord_can_view_their_rental_history(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        RentalHistory::create([
            'property_id' => $property->id,
            'tenant_id'   => $tenant->id,
            'landlord_id' => $landlord->id,
            'monthly_rent' => 2500,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subMonth(),
        ]);

        $this->actingAs($landlord)
             ->get(route('rental-history.index'))
             ->assertOk();
    }

    public function test_tenant_cannot_view_another_tenants_rental_history_record(): void
    {
        $landlord  = $this->makeLandlord();
        $tenantA   = $this->makeTenant();
        $tenantB   = $this->makeTenant();
        $property  = $this->makeProperty($landlord);

        $record = RentalHistory::create([
            'property_id' => $property->id,
            'tenant_id'   => $tenantB->id,
            'landlord_id' => $landlord->id,
            'monthly_rent' => 2500,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subMonth(),
        ]);

        $this->actingAs($tenantA)
             ->get(route('rental-history.show', $record))
             ->assertForbidden();
    }

    public function test_tenant_can_view_their_own_rental_history_record(): void
    {
        $landlord = $this->makeLandlord();
        $tenant   = $this->makeTenant();
        $property = $this->makeProperty($landlord);

        $record = RentalHistory::create([
            'property_id' => $property->id,
            'tenant_id'   => $tenant->id,
            'landlord_id' => $landlord->id,
            'monthly_rent' => 2500,
            'start_date'   => now()->subYear(),
            'end_date'     => now()->subMonth(),
        ]);

        $this->actingAs($tenant)
             ->get(route('rental-history.show', $record))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('RentalHistory/Show'));
    }
}
