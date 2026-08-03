<?php

namespace Tests\Feature\Admin;

use App\Models\UtilityType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyAnalyticsTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_property_analytics(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord);

        $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page
                 ->component('Admin/Analytics/Properties')
                 ->has('summary')
                 ->has('byProvince')
                 ->has('byDistrict')
                 ->has('byType')
                 ->has('byListing')
                 ->has('byUtility')
             );
    }

    public function test_non_admin_cannot_access_property_analytics(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.analytics.properties.index'))
             ->assertForbidden();
    }

    public function test_summary_reflects_seeded_properties(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord, ['price' => 1000, 'listing_type' => 'rent']);
        $this->makeProperty($landlord, ['price' => 3000, 'listing_type' => 'sale']);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index'));

        $response->assertInertia(fn ($page) => $page
            ->where('summary.total_properties', 2)
            ->where('summary.for_rent', 1)
            ->where('summary.for_sale', 1)
            ->where('summary.avg_price', fn ($v) => (float) $v === 2000.0)
        );
    }

    public function test_filters_by_property_type(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord, ['property_type' => 'residential']);
        $this->makeProperty($landlord, ['property_type' => 'commercial']);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index', ['property_type' => 'commercial']));

        $response->assertInertia(fn ($page) => $page->where('summary.total_properties', 1));
    }

    public function test_filters_by_province(): void
    {
        $landlord = $this->makeLandlord();
        $propertyA = $this->makeProperty($landlord);
        $this->makeProperty($landlord); // different province via makeLocation's unique seq

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index', ['province_id' => $propertyA->province_id]));

        $response->assertInertia(fn ($page) => $page->where('summary.total_properties', 1));
    }

    public function test_filters_by_utility(): void
    {
        $landlord = $this->makeLandlord();
        $withUtility = $this->makeProperty($landlord);
        $this->makeProperty($landlord);

        $utilityType = UtilityType::create(['name' => 'Power', 'icon' => '⚡', 'is_active' => true, 'sort_order' => 1]);
        $withUtility->utilities()->attach($utilityType->id);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index', ['utility_type_id' => $utilityType->id]));

        $response->assertInertia(fn ($page) => $page->where('summary.total_properties', 1));
    }

    public function test_byutility_breakdown_counts_attached_properties(): void
    {
        $landlord = $this->makeLandlord();
        $p1 = $this->makeProperty($landlord);
        $p2 = $this->makeProperty($landlord);

        $power = UtilityType::create(['name' => 'Power', 'icon' => '⚡', 'is_active' => true, 'sort_order' => 1]);
        $water = UtilityType::create(['name' => 'Water', 'icon' => '💧', 'is_active' => true, 'sort_order' => 2]);
        $p1->utilities()->attach($power->id);
        $p2->utilities()->attach([$power->id, $water->id]);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.index'));

        $byUtility = collect($response->viewData('page')['props']['byUtility']);
        $this->assertEquals(2, $byUtility->firstWhere('label', 'Power')->total);
        $this->assertEquals(1, $byUtility->firstWhere('label', 'Water')->total);
    }

    public function test_export_csv_returns_csv_response(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.export.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_pdf_returns_pdf_response(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord);

        $response = $this->actingAs($this->makeAdmin())
             ->get(route('admin.analytics.properties.export.pdf'));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_non_admin_cannot_export(): void
    {
        $this->actingAs($this->makeTenant())
             ->get(route('admin.analytics.properties.export.csv'))
             ->assertForbidden();
    }
}
