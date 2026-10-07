<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminUserDetailsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_user_details(): void
    {
        $admin = $this->makeAdmin();
        $user  = $this->makeTenant();

        $this->actingAs($admin)
             ->get(route('admin.users.show', $user))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Users/Show')
                 ->where('user.id', $user->id)
                 ->where('user.email', $user->email)
                 ->has('user.role_model')
                 ->has('user.properties_count')
                 ->has('user.tour_requests_count')
             );
    }

    public function test_user_details_include_landlord_application(): void
    {
        $admin       = $this->makeAdmin();
        $applicant   = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->get(route('admin.users.show', $applicant))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Users/Show')
                 ->where('user.landlord_application.id', $application->id)
                 ->where('user.landlord_application.status', $application->status)
             );
    }

    public function test_non_admin_cannot_view_user_details(): void
    {
        $tenant = $this->makeTenant();
        $other  = $this->makeTenant();

        $this->actingAs($tenant)
             ->get(route('admin.users.show', $other))
             ->assertForbidden();
    }
}
