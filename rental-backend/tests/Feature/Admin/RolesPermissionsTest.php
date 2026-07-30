<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class RolesPermissionsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── Roles ─────────────────────────────────────────────────────────

    public function test_admin_can_view_roles_index(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get(route('admin.roles.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Roles/Index'));
    }

    public function test_admin_can_create_role(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post(route('admin.roles.store'), [
                 'name'         => 'moderator',
                 'display_name' => 'Moderator',
                 'description'  => 'Content moderator',
             ])
             ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => 'moderator']);
    }

    public function test_admin_can_update_role(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
             ->put(route('admin.roles.update', $this->tenantRole), [
                 'name'         => 'tenant',
                 'display_name' => 'Renter',
             ])
             ->assertRedirect(route('admin.roles.index'));

        $this->assertEquals('Renter', $this->tenantRole->fresh()->display_name);
    }

    public function test_admin_cannot_delete_admin_role(): void
    {
        $this->actingAs($this->makeAdmin())
             ->delete(route('admin.roles.destroy', $this->adminRole))
             ->assertSessionHas('error');
    }

    public function test_admin_can_delete_non_admin_role(): void
    {
        $admin = $this->makeAdmin();
        $role  = \App\Models\Role::create(['name' => 'temp', 'display_name' => 'Temp', 'description' => '']);

        $this->actingAs($admin)
             ->delete(route('admin.roles.destroy', $role))
             ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'temp']);
    }

    // ── Permissions ───────────────────────────────────────────────────

    public function test_admin_can_create_permission(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post(route('admin.permissions.store'), [
                 'name'         => 'manage.reports',
                 'display_name' => 'Manage Reports',
             ])
             ->assertRedirect(route('admin.permissions.index'));

        $this->assertDatabaseHas('permissions', ['name' => 'manage.reports']);
    }

    public function test_admin_can_update_permission(): void
    {
        $admin      = $this->makeAdmin();
        $permission = Permission::create(['name' => 'view.reports', 'display_name' => 'View Reports']);

        $this->actingAs($admin)
             ->put(route('admin.permissions.update', $permission), [
                 'name'         => 'view.reports',
                 'display_name' => 'View All Reports',
             ])
             ->assertRedirect();

        $this->assertEquals('View All Reports', $permission->fresh()->display_name);
    }

    public function test_admin_can_delete_permission(): void
    {
        $admin      = $this->makeAdmin();
        $permission = Permission::create(['name' => 'temp.permission', 'display_name' => 'Temp']);

        $this->actingAs($admin)
             ->delete(route('admin.permissions.destroy', $permission))
             ->assertRedirect();

        $this->assertDatabaseMissing('permissions', ['name' => 'temp.permission']);
    }

    public function test_non_admin_cannot_manage_roles(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.roles.index'))
             ->assertForbidden();
    }
}
