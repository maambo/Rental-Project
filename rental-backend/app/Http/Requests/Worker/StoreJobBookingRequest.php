<?php

namespace App\Http\Requests\Worker;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobBookingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'worker_service_id' => ['nullable', 'exists:worker_services,id'],
            'job_description'   => ['required', 'string', 'max:1000'],
            'location'          => ['nullable', 'string', 'max:255'],
            'scheduled_date'    => ['nullable', 'date', 'after_or_equal:today'],
            'scheduled_time'    => ['nullable', 'string', 'max:10'],
            'client_notes'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
