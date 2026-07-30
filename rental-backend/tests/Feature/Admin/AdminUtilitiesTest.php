<?php

namespace Tests\Feature\Admin;

use App\Models\UtilityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AdminUtilitiesTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_utilities_index(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get(route('admin.utilities.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page->has('utilityTypes'));
    }

    public function test_admin_can_create_utility_type(): void
    {
        $this->actingAs($this->makeAdmin())
             ->post(route('admin.utilities.store'), [
                 'name' => 'Water',
                 'icon' => '💧',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('utility_types', ['name' => 'Water']);
    }

    public function test_admin_can_update_utility_type(): void
    {
        $admin   = $this->makeAdmin();
        $utility = UtilityType::create(['name' => 'Electricity', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($admin)
             ->put(route('admin.utilities.update', $utility), [
                 'name'      => 'Power',
                 'is_active' => true,
             ])
             ->assertRedirect();

        $this->assertEquals('Power', $utility->fresh()->name);
    }

    public function test_admin_soft_deletes_utility_by_deactivating(): void
    {
        $admin   = $this->makeAdmin();
        $utility = UtilityType::create(['name' => 'Cable TV', 'is_active' => true, 'sort_order' => 2]);

        $this->actingAs($admin)
             ->delete(route('admin.utilities.destroy', $utility))
             ->assertRedirect();

        $this->assertFalse((bool) $utility->fresh()->is_active);
        $this->assertDatabaseHas('utility_types', ['name' => 'Cable TV']); // not hard-deleted
    }

    public function test_admin_can_add_option_to_utility(): void
    {
        $admin   = $this->makeAdmin();
        $utility = UtilityType::create(['name' => 'Internet', 'is_active' => true, 'sort_order' => 3]);

        $this->actingAs($admin)
             ->post(route('admin.utilities.options.store', $utility), [
                 'label' => 'Fibre',
                 'value' => 'fibre',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('utility_options', ['label' => 'Fibre', 'value' => 'fibre']);
    }

    public function test_admin_can_delete_utility_option(): void
    {
        $admin   = $this->makeAdmin();
        $utility = UtilityType::create(['name' => 'Gas', 'is_active' => true, 'sort_order' => 4]);
        $option  = $utility->options()->create(['label' => 'LPG', 'value' => 'lpg', 'sort_order' => 1]);

        $this->actingAs($admin)
             ->delete(route('admin.utilities.options.destroy', $option))
             ->assertRedirect();

        $this->assertDatabaseMissing('utility_options', ['value' => 'lpg']);
    }

    public function test_non_admin_cannot_manage_utilities(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.utilities.index'))
             ->assertForbidden();
    }
}
