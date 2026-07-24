<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'trade_category_id' => ['required', 'exists:trade_categories,id'],
            'town_id'           => ['nullable', 'exists:towns,id'],
            'tagline'           => ['required', 'string', 'max:120'],
            'bio'               => ['required', 'string', 'max:2000'],
            'experience_years'  => ['required', 'integer', 'min:0', 'max:50'],
            'phone'             => ['required', 'string', 'max:20'],
            'service_radius_km' => ['nullable', 'integer', 'min:1', 'max:500'],
            'profile_photo'     => ['nullable', 'image', 'max:3072'],
            'certificate'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
