<?php

namespace Tests\Feature\Property;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class PropertyApprovalTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_approve_a_pending_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, [
            'approval_status'      => 'pending',
            'is_visible_in_search' => false,
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.properties.approve', $property))
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('properties', [
            'id'                   => $property->id,
            'approval_status'      => 'approved',
            'is_visible_in_search' => true,
        ]);
    }

    public function test_approved_property_appears_in_search(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, [
            'approval_status'      => 'pending',
            'is_visible_in_search' => false,
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.properties.approve', $property));

        $property->refresh();
        $this->assertTrue((bool) $property->is_visible_in_search);
        $this->assertEquals('approved', $property->approval_status);
        $this->assertEquals('available', $property->availability_status);
    }

    public function test_admin_can_reject_a_property_with_reason(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['approval_status' => 'pending']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.properties.reject', $property), [
                'rejection_reason' => 'Poor image quality.',
            ])
            ->assertRedirect(route('admin.properties.index'));

        $this->assertDatabaseHas('properties', [
            'id'                   => $property->id,
            'approval_status'      => 'rejected',
            'is_visible_in_search' => false,
            'rejection_reason'     => 'Poor image quality.',
        ]);
    }

    public function test_admin_reject_requires_a_reason(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['approval_status' => 'pending']);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.properties.reject', $property), [])
            ->assertSessionHasErrors('rejection_reason');
    }

    public function test_non_admin_cannot_approve_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['approval_status' => 'pending']);

        $this->actingAs($this->makeTenant())
            ->post(route('admin.properties.approve', $property))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_reject_property(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord, ['approval_status' => 'pending']);

        $this->actingAs($this->makeLandlord())
            ->post(route('admin.properties.reject', $property), ['rejection_reason' => 'bad'])
            ->assertForbidden();
    }

    public function test_admin_can_view_property_list(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.properties.index'))
            ->assertOk();
    }

    public function test_admin_can_view_property_detail(): void
    {
        $landlord = $this->makeLandlord();
        $property = $this->makeProperty($landlord);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.properties.show', $property))
            ->assertOk();
    }
}
