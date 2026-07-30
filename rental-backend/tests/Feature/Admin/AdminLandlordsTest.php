<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminLandlordsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_landlords_index(): void
    {
        $admin    = $this->makeAdmin();
        $landlord = $this->makeLandlord();

        $this->actingAs($admin)
             ->get(route('admin.landlords.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Landlords/Index')
                 ->has('landlords')
             );
    }

    public function test_admin_can_view_landlord_profile(): void
    {
        $admin    = $this->makeAdmin();
        $landlord = $this->makeLandlord();

        $this->actingAs($admin)
             ->get(route('admin.landlords.show', $landlord->id))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Landlords/Show'));
    }

    public function test_non_admin_cannot_access_landlord_list(): void
    {
        $tenant = $this->makeTenant();
        $this->actingAs($tenant)
             ->get(route('admin.landlords.index'))
             ->assertForbidden();
    }
}
