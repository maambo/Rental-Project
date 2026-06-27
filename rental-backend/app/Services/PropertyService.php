<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PropertyService
{
    /**
     * Attach uploaded image files to a property.
     */
    public function storeImages(Property $property, array $images): void
    {
        $existingCount = $property->images()->count();

        foreach ($images as $index => $image) {
            /** @var UploadedFile $image */
            $path = $image->store('properties', 'public');

            PropertyImage::create([
                'property_id' => $property->id,
                'image_url'   => $path,
                'is_primary'  => ($existingCount + $index) === 0,
                'order'       => $existingCount + $index,
            ]);
        }
    }

    /**
     * Delete selected images from a property (by image IDs).
     */
    public function deleteImages(Property $property, array $imageIds): void
    {
        $images = $property->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image_url);
            $image->delete();
        }
    }

    /**
     * Sync utility options on a property.
     * $optionIds is an array of utility_option IDs keyed or plain.
     * The pivot stores (property_id, utility_type_id, utility_option_id).
     */
    public function syncUtilities(Property $property, array $optionIds): void
    {
        if (empty($optionIds)) {
            $property->utilities()->detach();
            return;
        }

        $rows = \App\Models\UtilityOption::whereIn('id', $optionIds)
            ->get(['id', 'utility_type_id']);

        // Build sync array: utility_type_id => [utility_option_id => X]
        $sync = [];
        foreach ($rows as $option) {
            $sync[$option->utility_type_id] = ['utility_option_id' => $option->id];
        }

        $property->utilities()->sync($sync);
    }

    /**
     * Soft-delete a property and remove all its stored image files.
     */
    public function deleteProperty(Property $property): void
    {
        foreach ($property->images as $image) {
            Storage::disk('public')->delete($image->image_url);
        }

        $property->images()->delete();
        $property->delete();
    }
}
