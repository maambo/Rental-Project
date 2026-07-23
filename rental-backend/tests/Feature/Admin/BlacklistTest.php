<?php

namespace Tests\Feature\Admin;

use App\Models\Blacklist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class BlacklistTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_admin_can_view_blacklist(): void
    {
        $this->actingAs($this->makeAdmin())
            ->get(route('admin.blacklist.index'))
            ->assertOk();
    }

    public function test_admin_can_add_user_to_blacklist(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.blacklist.store'), [
                'nrc_passport' => '123456/78/1',
                'email'        => 'bad@example.com',
                'reason'       => 'Attempted fraud.',
                'type'         => 'fraud',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('blacklists', [
            'nrc_passport' => '123456/78/1',
            'email'        => 'bad@example.com',
            'type'         => 'fraud',
        ]);
    }

    public function test_duplicate_nrc_in_blacklist_is_rejected(): void
    {
        Blacklist::create([
            'nrc_passport'   => '123456/78/1',
            'reason'         => 'First entry',
            'type'           => 'fraud',
            'blacklisted_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($this->makeAdmin())
            ->post(route('admin.blacklist.store'), [
                'nrc_passport' => '123456/78/1',
                'reason'       => 'Duplicate entry',
                'type'         => 'fraud',
            ])
            ->assertSessionHasErrors('nrc_passport');
    }

    public function test_admin_can_remove_from_blacklist(): void
    {
        $entry = Blacklist::create([
            'nrc_passport'   => '123456/78/1',
            'reason'         => 'Test',
            'type'           => 'fraud',
            'blacklisted_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.blacklist.destroy', $entry))
            ->assertRedirect();

        $this->assertDatabaseMissing('blacklists', ['id' => $entry->id]);
    }

    public function test_non_admin_cannot_access_blacklist(): void
    {
        $this->actingAs($this->makeTenant())
            ->get(route('admin.blacklist.index'))
            ->assertForbidden();
    }

    public function test_non_admin_cannot_add_to_blacklist(): void
    {
        $this->actingAs($this->makeLandlord())
            ->post(route('admin.blacklist.store'), [
                'nrc_passport' => '123456/78/1',
                'reason'       => 'Test',
                'type'         => 'fraud',
            ])
            ->assertForbidden();
    }

    public function test_blacklist_type_must_be_valid(): void
    {
        $this->actingAs($this->makeAdmin())
            ->post(route('admin.blacklist.store'), [
                'nrc_passport' => '123456/78/1',
                'reason'       => 'Test',
                'type'         => 'invalid_type',
            ])
            ->assertSessionHasErrors('type');
    }
}
