<?php

namespace App\Http\Requests\LandlordApplication;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLandlordApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address'               => 'required|string|max:255',
            'province'              => 'required|string|max:100',
            'town'                  => 'required|string|max:100',
            'landlord_type'         => 'required|in:private_landlord,agent',
            'id_document'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'proof_of_address'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_certificate'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie'                => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'video_walkthrough'     => 'nullable|file|mimes:mp4,mov,qt|max:20480',
            'business_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}
