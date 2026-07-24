<?php

namespace App\Services;

use App\Models\WorkerPortfolioPhoto;
use App\Models\WorkerProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WorkerProfileService
{
    public function storeProfilePhoto(WorkerProfile $profile, UploadedFile $photo): string
    {
        if ($profile->profile_photo_url) {
            Storage::disk('public')->delete($profile->profile_photo_url);
        }

        $path = $photo->store('workers/photos', 'public');
        $profile->update(['profile_photo_url' => $path]);

        return $path;
    }

    public function storeCertificate(WorkerProfile $profile, UploadedFile $certificate): string
    {
        if ($profile->certificate_url) {
            Storage::disk('public')->delete($profile->certificate_url);
        }

        $path = $certificate->store('workers/certificates', 'public');
        $profile->update(['certificate_url' => $path]);

        return $path;
    }

    public function addPortfolioPhoto(WorkerProfile $profile, UploadedFile $photo, ?string $caption = null): WorkerPortfolioPhoto
    {
        $path       = $photo->store('workers/portfolio', 'public');
        $sortOrder  = $profile->portfolioPhotos()->max('sort_order') + 1;

        return $profile->portfolioPhotos()->create([
            'image_url'  => $path,
            'caption'    => $caption,
            'sort_order' => $sortOrder,
        ]);
    }

    public function deletePortfolioPhoto(WorkerPortfolioPhoto $portfolioPhoto): void
    {
        Storage::disk('public')->delete($portfolioPhoto->image_url);
        $portfolioPhoto->delete();
    }
}
