<?php

namespace App\Http\Requests\LandlordApplication;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandlordApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'phone'                 => 'required|string|max:20',
            'nrc_passport'          => 'required|string|max:50|unique:landlord_applications,nrc_passport',
            'address'               => 'required|string|max:255',
            'province'              => 'required|string|max:100',
            'town'                  => 'required|string|max:100',
            'landlord_type'         => 'required|in:private_landlord,agent',
            'id_document'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'proof_of_address'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'tax_certificate'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie'                => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'video_walkthrough'     => 'nullable|file|mimes:mp4,mov,qt|max:20480',
            'business_registration' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }
}
