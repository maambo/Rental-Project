<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkerProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'trade_category_id' => ['sometimes', 'exists:trade_categories,id'],
            'town_id'           => ['nullable', 'exists:towns,id'],
            'tagline'           => ['sometimes', 'string', 'max:120'],
            'bio'               => ['sometimes', 'string', 'max:2000'],
            'experience_years'  => ['sometimes', 'integer', 'min:0', 'max:50'],
            'phone'             => ['sometimes', 'string', 'max:20'],
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active'         => ['sometimes', 'boolean'],
            'profile_photo'     => ['nullable', 'image', 'max:3072'],
            'certificate'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
