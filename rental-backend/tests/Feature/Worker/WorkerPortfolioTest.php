<?php

namespace Tests\Feature\Worker;

use App\Models\WorkerPortfolioPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RentalTestSupport;

class WorkerPortfolioTest extends TestCase
{
    use RefreshDatabase, RentalTestSupport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
        Storage::fake('public');
    }

    // ── index ─────────────────────────────────────────────────────────

    public function test_worker_with_profile_can_view_portfolio(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user);

        $profile->portfolioPhotos()->create([
            'image_url'  => 'workers/portfolio/sample.jpg',
            'caption'    => 'Recent job',
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
             ->get(route('worker.portfolio.index'))
             ->assertOk()
             ->assertInertia(fn ($page) => $page
                 ->component('Worker/Portfolio/Index')
                 ->has('photos')
             );
    }

    public function test_worker_without_profile_gets_404_on_portfolio(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->get(route('worker.portfolio.index'))
             ->assertNotFound();
    }

    // ── store ─────────────────────────────────────────────────────────

    public function test_worker_can_upload_portfolio_photo(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user);

        $this->actingAs($user)
             ->post(route('worker.portfolio.store'), [
                 'photo'   => UploadedFile::fake()->image('job.jpg', 800, 600),
                 'caption' => 'Plumbing repair completed',
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('worker_portfolio_photos', [
            'worker_profile_id' => $profile->id,
            'caption'           => 'Plumbing repair completed',
        ]);

        $photo = $profile->portfolioPhotos()->first();
        Storage::disk('public')->assertExists($photo->image_url);
    }

    public function test_portfolio_photo_upload_requires_image_file(): void
    {
        $user = $this->makeTenant();
        $this->makeWorkerProfile($user);

        $this->actingAs($user)
             ->post(route('worker.portfolio.store'), [
                 'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
             ])
             ->assertSessionHasErrors('photo');
    }

    public function test_worker_without_profile_cannot_upload_photo(): void
    {
        $user = $this->makeTenant();

        $this->actingAs($user)
             ->post(route('worker.portfolio.store'), [
                 'photo' => UploadedFile::fake()->image('photo.jpg'),
             ])
             ->assertNotFound();
    }

    public function test_portfolio_upload_rejected_when_limit_reached(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user);

        // Seed 20 photos directly
        for ($i = 1; $i <= 20; $i++) {
            $profile->portfolioPhotos()->create([
                'image_url'  => "workers/portfolio/photo{$i}.jpg",
                'sort_order' => $i,
            ]);
        }

        $this->actingAs($user)
             ->post(route('worker.portfolio.store'), [
                 'photo' => UploadedFile::fake()->image('extra.jpg'),
             ])
             ->assertStatus(422);
    }

    // ── destroy ───────────────────────────────────────────────────────

    public function test_worker_can_delete_their_own_photo(): void
    {
        $user    = $this->makeTenant();
        $profile = $this->makeWorkerProfile($user);

        $fakePath = 'workers/portfolio/mywork.jpg';
        Storage::disk('public')->put($fakePath, 'fake-image-data');

        $photo = $profile->portfolioPhotos()->create([
            'image_url'  => $fakePath,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
             ->delete(route('worker.portfolio.destroy', $photo))
             ->assertRedirect();

        $this->assertDatabaseMissing('worker_portfolio_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing($fakePath);
    }

    public function test_worker_cannot_delete_another_workers_photo(): void
    {
        $userA    = $this->makeTenant();
        $userB    = $this->makeTenant();
        $profileA = $this->makeWorkerProfile($userA);
        $this->makeWorkerProfile($userB);

        $photo = $profileA->portfolioPhotos()->create([
            'image_url'  => 'workers/portfolio/other.jpg',
            'sort_order' => 1,
        ]);

        $this->actingAs($userB)
             ->delete(route('worker.portfolio.destroy', $photo))
             ->assertForbidden();

        $this->assertDatabaseHas('worker_portfolio_photos', ['id' => $photo->id]);
    }

    public function test_worker_without_profile_cannot_delete_photo(): void
    {
        $ownerUser = $this->makeTenant();
        $profile   = $this->makeWorkerProfile($ownerUser);
        $photo     = $profile->portfolioPhotos()->create([
            'image_url'  => 'workers/portfolio/photo.jpg',
            'sort_order' => 1,
        ]);

        $otherUser = $this->makeTenant(); // no profile

        $this->actingAs($otherUser)
             ->delete(route('worker.portfolio.destroy', $photo))
             ->assertForbidden();
    }
}
