<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NrcUniquenessTest extends TestCase
{
    use RefreshDatabase;

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test User',
            'email'                 => 'user@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234567',
            'id_type'               => 'nrc',
            'nrc_passport'          => '123456/78/1',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ], $overrides);
    }

    public function test_registration_requires_phone(): void
    {
        Storage::fake('public');

        $payload = $this->registrationPayload();
        unset($payload['phone']);

        $this->post('/register', $payload)->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_requires_id_type(): void
    {
        Storage::fake('public');

        $this->post('/register', $this->registrationPayload(['id_type' => '']))
            ->assertSessionHasErrors('id_type');
    }

    public function test_registration_requires_id_document(): void
    {
        Storage::fake('public');

        $payload = $this->registrationPayload();
        unset($payload['id_document']);

        $this->post('/register', $payload)->assertSessionHasErrors('id_document');
    }

    public function test_registration_requires_selfie(): void
    {
        Storage::fake('public');

        $payload = $this->registrationPayload();
        unset($payload['selfie']);

        $this->post('/register', $payload)->assertSessionHasErrors('selfie');
    }

    public function test_duplicate_nrc_is_rejected_on_registration(): void
    {
        Storage::fake('public');

        // First registration succeeds
        $this->post('/register', $this->registrationPayload())->assertRedirect();

        // Log out so the guest middleware doesn't redirect the second request
        $this->post('/logout');

        // Second registration with same NRC must fail
        $this->post('/register', $this->registrationPayload([
            'email' => 'other@example.com',
        ]))->assertSessionHasErrors('nrc_passport');
    }

    public function test_passport_type_is_accepted(): void
    {
        Storage::fake('public');

        $this->post('/register', $this->registrationPayload([
            'id_type'      => 'passport',
            'nrc_passport' => 'A1234567',
        ]))->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_nrc_is_stored_on_user(): void
    {
        Storage::fake('public');

        $this->post('/register', $this->registrationPayload())->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'        => 'user@example.com',
            'id_type'      => 'nrc',
            'nrc_passport' => '123456/78/1',
            'phone'        => '+260971234567',
        ]);
    }
}
