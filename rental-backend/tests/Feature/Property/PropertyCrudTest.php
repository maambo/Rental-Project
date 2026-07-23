<?php

namespace Tests\Feature\Property;

use App\Models\LandlordApplication;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyCrudTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function validPropertyPayload(array $overrides = []): array
    {
        [$province, $district, $town] = $this->makeLocation();

        return array_merge([
            'province_id'      => $province->id,
            'district_id'      => $district->id,
            'town_id'          => $town->id,
            'street_address'   => '123 Test Street',
            'latitude'         => -15.4,
            'longitude'        => 28.3,
            'property_type'    => 'residential',
            'property_subtype' => 'apartment',
            'listing_type'     => 'rent',
            'title'            => 'My Test Apartment',
            'description'      => 'A lovely test apartment.',
            'price'            => 2500,
            'bedrooms'         => 2,
            'bathrooms'        => 1,
            'images'           => [UploadedFile::fake()->image('photo.jpg')],
        ], $overrides);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_landlord_without_approved_application_is_redirected_from_create(): void
    {
        $landlord = $this->makeLandlord();
        // No LandlordApplication record for this user

        $this->actingAs($landlord)
            ->get(route('landlord.properties.create'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_landlord_with_approved_application_can_view_create_form(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeLandlordApplication($landlord, ['status' => 'approved']);

        $this->actingAs($landlord)
            ->get(route('landlord.properties.create'))
            ->assertOk();
    }

    public function test_landlord_can_store_a_property(): void
    {
        Storage::fake('public');

        $landlord = $this->makeLandlord();
        $this->makeLandlordApplication($landlord, ['status' => 'approved']);

        $this->actingAs($landlord)
            ->post(route('landlord.properties.store'), $this->validPropertyPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('properties', [
            'landlord_id'          => $landlord->id,
            'title'                => 'My Test Apartment',
            'approval_status'      => 'pending',
            'is_visible_in_search' => false,
        ]);
    }

    public function test_new_property_is_always_pending_and_not_visible(): void
    {
        Storage::fake('public');

        $landlord = $this->makeLandlord();
        $this->makeLandlordApplication($landlord, ['status' => 'approved']);

        $this->actingAs($landlord)
            ->post(route('landlord.properties.store'), $this->validPropertyPayload([
                // Even if someone tries to smuggle these in via the payload, they should be overridden
                'approval_status'      => 'approved',
                'is_visible_in_search' => true,
            ]))->assertRedirect();

        $this->assertDatabaseHas('properties', [
            'landlord_id'          => $landlord->id,
            'approval_status'      => 'pending',
            'is_visible_in_search' => false,
        ]);
    }

    public function test_property_requires_at_least_one_image(): void
    {
        Storage::fake('public');

        $landlord = $this->makeLandlord();
        $this->makeLandlordApplication($landlord, ['status' => 'approved']);

        $payload = $this->validPropertyPayload();
        unset($payload['images']);

        $this->actingAs($landlord)
            ->post(route('landlord.properties.store'), $payload)
            ->assertSessionHasErrors('images');
    }

    public function test_tenant_cannot_create_properties(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeTenant())
            ->post(route('landlord.properties.store'), $this->validPropertyPayload())
            ->assertForbidden();
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function test_landlord_can_view_their_property_list(): void
    {
        $landlord = $this->makeLandlord();
        $this->makeProperty($landlord);

        $this->actingAs($landlord)
            ->get(route('landlord.properties.index'))
            ->assertOk();
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function test_landlord_can_view_edit_form_for_their_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);
        $this->makeLandlordApplication($landlord, ['status' => 'approved']);

        $this->actingAs($landlord)
            ->get(route('landlord.properties.edit', $property))
            ->assertOk();
    }

    public function test_landlord_cannot_edit_another_landlords_property(): void
    {
        $landlord1 = $this->makeLandlord();
        $landlord2 = $this->makeLandlord();
        $property  = $this->makeProperty($landlord1);

        // The controller scopes by landlord_id and returns 404 (not 403) for other landlords' properties
        $this->actingAs($landlord2)
            ->get(route('landlord.properties.edit', $property))
            ->assertNotFound();
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function test_landlord_can_delete_their_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->actingAs($landlord)
            ->delete(route('landlord.properties.destroy', $property))
            ->assertRedirect();

        // Property model uses SoftDeletes — record remains but is soft-deleted
        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    }

    public function test_landlord_cannot_delete_another_landlords_property(): void
    {
        $landlord1 = $this->makeLandlord();
        $landlord2 = $this->makeLandlord();
        $property  = $this->makeProperty($landlord1);

        // The controller scopes by landlord_id and returns 404 (not 403) for other landlords' properties
        $this->actingAs($landlord2)
            ->delete(route('landlord.properties.destroy', $property))
            ->assertNotFound();

        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    }
}
