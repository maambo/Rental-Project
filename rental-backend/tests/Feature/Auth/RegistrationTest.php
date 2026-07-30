<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Storage::fake('public');

        $response = $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234567',
            'id_type'               => 'nrc',
            'nrc_passport'          => '123456/78/1',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Regression: registration used to leave role_id null, which made every
     * `role:tenant` route (apply, tour request, review) return 403 for anyone
     * who signed up through the form.
     */
    public function test_registered_user_receives_the_tenant_role(): void
    {
        Storage::fake('public');
        $this->createRoles();

        $this->post('/register', [
            'name'                  => 'Role Check',
            'email'                 => 'rolecheck@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234500',
            'id_type'               => 'nrc',
            'nrc_passport'          => '654321/78/1',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'rolecheck@example.com')->firstOrFail();

        $this->assertNotNull($user->role_id, 'Registered user must be given a role.');
        $this->assertTrue($user->isTenant());
        $this->assertSame('tenant', $user->roleModel->name);
    }

    public function test_registered_user_can_reach_a_tenant_only_route(): void
    {
        Storage::fake('public');
        $this->createRoles();

        $property = $this->makeProperty($this->makeLandlord());

        $this->post('/register', [
            'name'                  => 'Applying Tenant',
            'email'                 => 'applicant@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'phone'                 => '+260971234501',
            'id_type'               => 'nrc',
            'nrc_passport'          => '654321/78/2',
            'id_document'           => UploadedFile::fake()->image('id.jpg'),
            'selfie'                => UploadedFile::fake()->image('selfie.jpg'),
        ]);

        $this->get(route('properties.apply', $property))->assertOk();
    }
}
