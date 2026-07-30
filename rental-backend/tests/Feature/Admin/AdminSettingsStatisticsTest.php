<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminSettingsStatisticsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    // ── Settings ──────────────────────────────────────────────────────

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get(route('admin.settings.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Settings/Index'));
    }

    public function test_settings_update_redirects_back(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post(route('admin.settings.update'))
             ->assertRedirect();
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.settings.index'))
             ->assertForbidden();
    }

    // ── Statistics ────────────────────────────────────────────────────

    public function test_admin_can_view_statistics_dashboard(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get(route('admin.statistics.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->component('Admin/Statistics/Index')
                 ->has('stats')
                 ->has('growthData')
                 ->has('recentTransactions')
             );
    }

    public function test_statistics_contains_expected_sections(): void
    {
        $this->makeLandlord();
        $this->makeTenant();

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.statistics.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('stats.users.total', fn ($v) => $v >= 2)
        );
    }
}
