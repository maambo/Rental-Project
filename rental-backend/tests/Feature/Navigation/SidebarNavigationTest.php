<?php

namespace Tests\Feature\Navigation;

use App\Models\LandlordApplication;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;
    private Role $landlordRole;
    private Role $tenantRole;
    private Role $applicantRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole     = Role::create(['name' => 'admin',              'display_name' => 'Administrator',       'description' => '']);
        $this->landlordRole  = Role::create(['name' => 'landlord',           'display_name' => 'Landlord',            'description' => '']);
        $this->tenantRole    = Role::create(['name' => 'tenant',             'display_name' => 'Tenant',              'description' => '']);
        $this->applicantRole = Role::create(['name' => 'applicant_landlord', 'display_name' => 'Applicant Landlord',  'description' => '']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    private function landlord(): User
    {
        return User::factory()->create(['role_id' => $this->landlordRole->id]);
    }

    private function tenant(): User
    {
        return User::factory()->create(['role_id' => $this->tenantRole->id]);
    }

    private function applicant(): User
    {
        return User::factory()->create(['role_id' => $this->applicantRole->id]);
    }

    // ── Unauthenticated redirects ─────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_admin_routes(): void
    {
        $this->get('/admin/applications')->assertRedirect('/login');
        $this->get('/admin/properties')->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_landlord_routes(): void
    {
        $this->get('/landlord/dashboard')->assertRedirect('/login');
        $this->get('/landlord/properties')->assertRedirect('/login');
    }

    public function test_unauthenticated_user_is_redirected_from_tenant_routes(): void
    {
        $this->get('/my-applications')->assertRedirect('/login');
    }

    // ── Admin navigation ──────────────────────────────────────────────────────

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertOk();
    }

    public function test_admin_can_access_applications_list(): void
    {
        $this->actingAs($this->admin())->get('/admin/applications')->assertOk();
    }

    public function test_admin_can_access_properties_list(): void
    {
        $this->actingAs($this->admin())->get('/admin/properties')->assertOk();
    }

    public function test_admin_can_access_statistics(): void
    {
        $this->actingAs($this->admin())->get('/admin/statistics')->assertOk();
    }

    public function test_admin_can_access_users_list(): void
    {
        $this->actingAs($this->admin())->get('/admin/users')->assertOk();
    }

    public function test_admin_can_access_utilities(): void
    {
        $this->actingAs($this->admin())->get('/admin/utilities')->assertOk();
    }

    public function test_admin_can_access_roles_list(): void
    {
        $this->actingAs($this->admin())->get('/admin/roles')->assertOk();
    }

    public function test_admin_can_access_landlord_profiles(): void
    {
        $this->actingAs($this->admin())->get('/admin/landlords')->assertOk();
    }

    public function test_admin_can_access_settings(): void
    {
        $this->actingAs($this->admin())->get('/admin/settings')->assertOk();
    }

    public function test_admin_can_access_subscriptions(): void
    {
        $this->actingAs($this->admin())->get('/admin/subscriptions')->assertOk();
    }

    // ── Admin role protection ─────────────────────────────────────────────────

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->landlord())->get('/admin/applications')->assertForbidden();
        $this->actingAs($this->tenant())->get('/admin/properties')->assertForbidden();
    }

    // ── Landlord navigation ───────────────────────────────────────────────────

    public function test_landlord_can_access_landlord_dashboard(): void
    {
        $this->actingAs($this->landlord())->get('/landlord/dashboard')->assertOk();
    }

    public function test_landlord_can_access_their_properties(): void
    {
        $this->actingAs($this->landlord())->get('/landlord/properties')->assertOk();
    }

    public function test_landlord_can_access_property_applications(): void
    {
        $this->actingAs($this->landlord())->get('/landlord/property-applications')->assertOk();
    }

    public function test_landlord_can_access_tour_requests_page(): void
    {
        $this->actingAs($this->landlord())->get('/landlord/tour-requests')->assertOk();
    }

    public function test_landlord_can_access_maintenance_page(): void
    {
        $this->actingAs($this->landlord())->get('/landlord/maintenance')->assertOk();
    }

    public function test_landlord_can_access_messages(): void
    {
        $this->actingAs($this->landlord())->get('/chat')->assertOk();
    }

    // ── Landlord role protection ──────────────────────────────────────────────

    public function test_non_landlord_cannot_access_landlord_routes(): void
    {
        $this->actingAs($this->tenant())->get('/landlord/dashboard')->assertForbidden();
        $this->actingAs($this->admin())->get('/landlord/properties')->assertForbidden();
    }

    // ── Tenant navigation ─────────────────────────────────────────────────────

    public function test_tenant_can_access_dashboard(): void
    {
        $this->actingAs($this->tenant())->get('/dashboard')->assertOk();
    }

    public function test_tenant_can_access_their_applications(): void
    {
        $this->actingAs($this->tenant())->get('/my-applications')->assertOk();
    }

    public function test_tenant_can_access_my_rentals_page(): void
    {
        $this->actingAs($this->tenant())->get('/tenant/my-rentals')->assertOk();
    }

    public function test_tenant_can_access_messages(): void
    {
        $this->actingAs($this->tenant())->get('/chat')->assertOk();
    }

    public function test_tenant_can_access_maintenance_page(): void
    {
        $this->actingAs($this->tenant())->get('/tenant/maintenance')->assertOk();
    }

    public function test_tenant_can_access_rental_history(): void
    {
        $this->actingAs($this->tenant())->get('/rental-history')->assertOk();
    }

    // ── Tenant role protection ────────────────────────────────────────────────

    public function test_non_tenant_cannot_access_tenant_only_routes(): void
    {
        $this->actingAs($this->landlord())->get('/my-applications')->assertForbidden();
        $this->actingAs($this->admin())->get('/tenant/my-rentals')->assertForbidden();
    }

    // ── Applicant Landlord navigation ─────────────────────────────────────────

    public function test_applicant_can_access_dashboard(): void
    {
        $this->actingAs($this->applicant())->get('/dashboard')->assertOk();
    }

    private function makeApplication(User $user): void
    {
        LandlordApplication::create([
            'user_id'              => $user->id,
            'status'               => 'pending',
            'nrc_passport'         => 'NRC123456/78/1',
            'address'              => '123 Test Street',
            'province'             => 'Lusaka',
            'town'                 => 'Lusaka',
            'landlord_type'        => 'private_landlord',
            'id_document_url'      => 'docs/id.jpg',
            'proof_of_address_url' => 'docs/proof.jpg',
        ]);
    }

    public function test_applicant_can_access_application_status(): void
    {
        $user = $this->applicant();
        $this->makeApplication($user);
        $this->actingAs($user)->get('/landlord/application-status')->assertOk();
    }

    public function test_applicant_can_access_application_edit(): void
    {
        $user = $this->applicant();
        $this->makeApplication($user);
        $this->actingAs($user)->get('/landlord/application/edit')->assertOk();
    }

    public function test_applicant_can_access_help_support(): void
    {
        $this->actingAs($this->applicant())->get('/help-support')->assertOk();
    }

    // ── Shared authenticated links ────────────────────────────────────────────

    public function test_authenticated_users_can_access_profile(): void
    {
        $this->actingAs($this->admin())->get('/profile')->assertOk();
        $this->actingAs($this->landlord())->get('/profile')->assertOk();
        $this->actingAs($this->tenant())->get('/profile')->assertOk();
    }

    public function test_authenticated_users_can_access_public_home(): void
    {
        $this->actingAs($this->admin())->get('/')->assertOk();
        $this->actingAs($this->tenant())->get('/')->assertOk();
    }
}
