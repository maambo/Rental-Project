<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminWorkerVerificationTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_worker_index(): void
    {
        $admin  = $this->makeAdmin();
        $worker = $this->makeTenant();
        $this->makeWorkerProfile($worker);

        $this->actingAs($admin)
             ->get(route('admin.workers.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Workers/Index')->has('workers'));
    }

    public function test_admin_can_verify_worker(): void
    {
        $admin   = $this->makeAdmin();
        $worker  = $this->makeTenant();
        $profile = $this->makeWorkerProfile($worker, ['is_verified' => false]);

        $this->actingAs($admin)
             ->post(route('admin.workers.verify', $profile))
             ->assertRedirect();

        $this->assertTrue((bool) $profile->fresh()->is_verified);
    }

    public function test_admin_can_revoke_worker_verification(): void
    {
        $admin   = $this->makeAdmin();
        $worker  = $this->makeTenant();
        $profile = $this->makeWorkerProfile($worker, ['is_verified' => true]);

        $this->actingAs($admin)
             ->post(route('admin.workers.revoke', $profile))
             ->assertRedirect();

        $this->assertFalse((bool) $profile->fresh()->is_verified);
    }

    public function test_admin_can_toggle_featured_worker(): void
    {
        $admin   = $this->makeAdmin();
        $worker  = $this->makeTenant();
        $profile = $this->makeWorkerProfile($worker, ['is_featured' => false]);

        $this->actingAs($admin)
             ->post(route('admin.workers.toggle-featured', $profile))
             ->assertRedirect();

        $this->assertTrue((bool) $profile->fresh()->is_featured);
    }

    public function test_admin_can_create_trade_category(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post(route('admin.workers.categories.store'), [
                 'name' => 'Landscaping',
                 'icon' => '🌿',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('trade_categories', ['name' => 'Landscaping']);
    }

    public function test_admin_can_toggle_category_active_status(): void
    {
        $admin    = $this->makeAdmin();
        $category = \App\Models\TradeCategory::firstOrCreate(
            ['slug' => 'welding'],
            ['name' => 'Welding', 'icon' => '🔥', 'is_active' => true, 'sort_order' => 99]
        );

        $this->actingAs($admin)
             ->post(route('admin.workers.categories.toggle', $category))
             ->assertRedirect();

        $this->assertFalse((bool) $category->fresh()->is_active);
    }

    public function test_non_admin_cannot_access_worker_admin(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.workers.index'))
             ->assertForbidden();
    }
}
