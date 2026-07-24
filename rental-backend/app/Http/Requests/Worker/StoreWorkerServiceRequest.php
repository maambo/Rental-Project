<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkerServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'service_name'   => ['required', 'string', 'max:120'],
            'description'    => ['nullable', 'string', 'max:500'],
            'rate_type'      => ['required', Rule::in(['hourly', 'per_job', 'per_day'])],
            'base_rate'      => ['required', 'numeric', 'min:0'],
            'minimum_charge' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
