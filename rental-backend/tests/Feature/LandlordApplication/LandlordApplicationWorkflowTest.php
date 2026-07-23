<?php

namespace Tests\Feature\LandlordApplication;

use App\Models\LandlordApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class LandlordApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── Submission ────────────────────────────────────────────────────────────

    public function test_guest_can_view_application_form(): void
    {
        $this->get(route('landlord.apply'))->assertOk();
    }

    public function test_valid_application_creates_user_and_application(): void
    {
        Storage::fake('public');

        $this->post(route('landlord.apply.store'), [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234567',
            'id_type'               => 'nrc',
            'nrc_passport'          => '123456/78/1',
            'address'               => '123 Cairo Road',
            'province'              => 'Lusaka',
            'town'                  => 'Lusaka',
            'landlord_type'         => 'private_landlord',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'proof_of_address'      => UploadedFile::fake()->image('proof.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'nrc_passport' => '123456/78/1']);
        $this->assertDatabaseHas('landlord_applications', ['nrc_passport' => '123456/78/1', 'status' => 'pending']);
    }

    public function test_application_requires_selfie(): void
    {
        Storage::fake('public');

        $this->post(route('landlord.apply.store'), [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234567',
            'id_type'               => 'nrc',
            'nrc_passport'          => '123456/78/1',
            'address'               => '123 Cairo Road',
            'province'              => 'Lusaka',
            'town'                  => 'Lusaka',
            'landlord_type'         => 'private_landlord',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'proof_of_address'      => UploadedFile::fake()->image('proof.jpg'),
        ])->assertSessionHasErrors('selfie');
    }

    public function test_duplicate_nrc_is_rejected_on_landlord_application(): void
    {
        Storage::fake('public');

        // Register a tenant with that NRC first
        User::factory()->create(['nrc_passport' => '123456/78/1', 'role_id' => $this->tenantRole->id]);

        $this->post(route('landlord.apply.store'), [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234567',
            'id_type'               => 'nrc',
            'nrc_passport'          => '123456/78/1', // same NRC
            'address'               => '123 Cairo Road',
            'province'              => 'Lusaka',
            'town'                  => 'Lusaka',
            'landlord_type'         => 'private_landlord',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'proof_of_address'      => UploadedFile::fake()->image('proof.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertSessionHasErrors('nrc_passport');
    }

    // ── Admin review ──────────────────────────────────────────────────────────

    public function test_admin_can_mark_application_as_under_review(): void
    {
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.applications.under-review', $application))
            ->assertRedirect();

        $this->assertDatabaseHas('landlord_applications', [
            'id'     => $application->id,
            'status' => 'under_review',
        ]);
    }

    public function test_admin_can_approve_application_and_promote_user_to_landlord(): void
    {
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant, ['status' => 'under_review']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.applications.approve', $application))
            ->assertRedirect(route('admin.applications.index'));

        $this->assertDatabaseHas('landlord_applications', ['id' => $application->id, 'status' => 'approved']);
        $this->assertDatabaseHas('users', ['id' => $applicant->id, 'role_id' => $this->landlordRole->id]);
        // Starter subscription should be auto-created
        $this->assertDatabaseHas('subscriptions', ['user_id' => $applicant->id, 'status' => 'active']);
    }

    public function test_admin_can_reject_application_with_reason(): void
    {
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.applications.reject', $application), [
                'rejection_reason' => 'Incomplete documents.',
            ])
            ->assertRedirect(route('admin.applications.index'));

        $this->assertDatabaseHas('landlord_applications', [
            'id'               => $application->id,
            'status'           => 'rejected',
            'rejection_reason' => 'Incomplete documents.',
        ]);
    }

    public function test_admin_reject_requires_reason(): void
    {
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.applications.reject', $application), [])
            ->assertSessionHasErrors('rejection_reason');
    }

    // ── Applicant editing ─────────────────────────────────────────────────────

    public function test_applicant_can_view_and_edit_pending_application(): void
    {
        $applicant = $this->makeApplicant();
        $this->makeLandlordApplication($applicant, ['status' => 'pending']);

        $this->actingAs($applicant)->get(route('landlord.application.edit'))->assertOk();
    }

    public function test_applicant_cannot_edit_under_review_application(): void
    {
        $applicant = $this->makeApplicant();
        $this->makeLandlordApplication($applicant, ['status' => 'under_review']);

        // The controller redirects away for under_review status
        $this->actingAs($applicant)->get(route('landlord.application.edit'))->assertRedirect();
    }

    // ── Non-admin protection ──────────────────────────────────────────────────

    public function test_non_admin_cannot_approve_applications(): void
    {
        $applicant = $this->makeApplicant();
        $application = $this->makeLandlordApplication($applicant);

        $this->actingAs($this->makeTenant())
            ->post(route('admin.applications.approve', $application))
            ->assertForbidden();
    }
}
