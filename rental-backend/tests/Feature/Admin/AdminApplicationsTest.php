<?php

namespace Tests\Feature\Admin;

use App\Models\LandlordApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminApplicationsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_applications_index(): void
    {
        $admin = $this->makeAdmin();
        $applicant = $this->makeApplicant();
        $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->get(route('admin.applications.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Applications/Index')
                 ->has('applications')
                 ->has('stats')
             );
    }

    public function test_admin_can_view_single_application(): void
    {
        $admin     = $this->makeAdmin();
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->get(route('admin.applications.show', $application))
             ->assertOk();
    }

    public function test_admin_can_mark_application_under_review(): void
    {
        $admin       = $this->makeAdmin();
        $applicant   = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->post(route('admin.applications.under-review', $application))
             ->assertRedirect();

        $this->assertEquals('under_review', $application->fresh()->status);
    }

    public function test_admin_can_approve_application_and_promote_user(): void
    {
        $admin       = $this->makeAdmin();
        $applicant   = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->post(route('admin.applications.approve', $application))
             ->assertRedirect(route('admin.applications.index'));

        $this->assertEquals('approved', $application->fresh()->status);
        $this->assertEquals('landlord', $applicant->fresh()->roleModel->name);
    }

    public function test_admin_can_reject_application_with_reason(): void
    {
        $admin       = $this->makeAdmin();
        $applicant   = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->post(route('admin.applications.reject', $application), [
                 'rejection_reason' => 'Incomplete documentation.',
             ])
             ->assertRedirect(route('admin.applications.index'));

        $fresh = $application->fresh();
        $this->assertEquals('rejected', $fresh->status);
        $this->assertEquals('Incomplete documentation.', $fresh->rejection_reason);
    }

    public function test_reject_requires_reason(): void
    {
        $admin       = $this->makeAdmin();
        $applicant   = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($admin)
             ->post(route('admin.applications.reject', $application), [])
             ->assertSessionHasErrors('rejection_reason');
    }

    public function test_non_admin_cannot_access_applications(): void
    {
        $tenant = $this->makeTenant();
        $this->actingAs($tenant)
             ->get(route('admin.applications.index'))
             ->assertForbidden();
    }
}
