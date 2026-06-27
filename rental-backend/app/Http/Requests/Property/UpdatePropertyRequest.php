<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Location
            'province_id'      => 'required|exists:provinces,id',
            'district_id'      => 'required|exists:districts,id',
            'town_id'          => 'required|exists:towns,id',
            'street_address'   => 'required|string|max:255',
            'latitude'         => 'required|numeric|between:-18.1,-8.2',
            'longitude'        => 'required|numeric|between:21.9,33.7',

            // Classification
            'property_type'    => 'required|in:residential,commercial',
            'property_subtype' => 'required|in:house,apartment,room,farm,plot,shop,office_space,warehouse',
            'listing_type'     => 'required|in:rent,sale',

            // Core details
            'title'            => 'required|string|max:255',
            'description'      => 'required|string',
            'price'            => 'required|numeric|min:0',
            'bedrooms'         => 'nullable|integer|min:0',
            'bathrooms'        => 'nullable|integer|min:0',
            'square_feet'      => 'nullable|integer|min:0',
            'amenities'        => 'nullable|array',
            'amenities.*'      => 'string',

            // Image management
            'new_images'       => 'nullable|array|max:10',
            'new_images.*'     => 'image|mimes:jpeg,png,jpg|max:5120',
            'remove_images'    => 'nullable|array',
            'remove_images.*'  => 'integer|exists:property_images,id',

            // Utilities
            'utilities'        => 'nullable|array',
            'utilities.*'      => 'integer|exists:utility_options,id',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.between'  => 'Location must be within Zambia.',
            'longitude.between' => 'Location must be within Zambia.',
        ];
    }
}
