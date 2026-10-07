<?php

namespace Tests\Feature\Profile;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class AvatarUploadTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        Storage::fake('public');
    }

    public function test_user_can_upload_an_avatar(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->post(route('profile.avatar.update'), [
                 'avatar' => UploadedFile::fake()->image('me.jpg'),
             ])
             ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('avatars/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_uploading_a_new_avatar_deletes_the_previous_file(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $firstPath = $user->refresh()->avatar;

        $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $secondPath = $user->refresh()->avatar;

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }

    public function test_avatar_must_be_an_image_under_the_size_limit(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->post(route('profile.avatar.update'), [
                 'avatar' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
             ])
             ->assertSessionHasErrors('avatar');

        $this->actingAs($user)
             ->post(route('profile.avatar.update'), [
                 'avatar' => UploadedFile::fake()->image('huge.jpg')->size(3000),
             ])
             ->assertSessionHasErrors('avatar');

        $this->assertNull($user->refresh()->avatar);
    }

    public function test_user_can_remove_their_avatar(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ]);

        $path = $user->refresh()->avatar;

        $this->actingAs($user)
             ->delete(route('profile.avatar.destroy'))
             ->assertRedirect(route('profile.edit'));

        $this->assertNull($user->refresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_google_avatar_url_is_returned_as_is_and_never_deleted(): void
    {
        $user = $this->makeTenant();
        $user->forceFill(['avatar' => 'https://lh3.googleusercontent.com/a/photo.jpg'])->save();

        $this->assertSame('https://lh3.googleusercontent.com/a/photo.jpg', $user->avatar_url);
        $this->assertFalse($user->hasUploadedAvatar());

        // Removing it should just null the column — there is no local file to unlink.
        $this->actingAs($user)->delete(route('profile.avatar.destroy'))->assertRedirect();
        $this->assertNull($user->refresh()->avatar);
    }

    public function test_users_without_an_avatar_fall_back_to_generated_initials(): void
    {
        $user = $this->makeTenant();

        $this->assertStringContainsString('ui-avatars.com', $user->avatar_url);
    }

    public function test_guests_cannot_upload_an_avatar(): void
    {
        $this->post(route('profile.avatar.update'), [
            'avatar' => UploadedFile::fake()->image('me.jpg'),
        ])->assertRedirect(route('login'));
    }
}
