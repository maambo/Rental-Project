<?php

namespace App\Http\Requests\PropertyApplication;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyApplicationRequest extends FormRequest
{
    private ?Property $property = null;

    public function authorize(): bool { return true; }

    protected function resolveProperty(): Property
    {
        if (!$this->property) {
            $this->property = Property::findOrFail($this->property_id);
        }
        return $this->property;
    }

    public function rules(): array
    {
        $property = $this->resolveProperty();
        $isResidentialRent = $property->isResidentialRent();
        $isCommercial      = $property->isCommercial();

        return [
            'property_id'         => 'required|exists:properties,id',
            'message'             => 'nullable|string|max:1000',
            'preferred_move_in'   => 'nullable|date|after:today',
            'additional_comments' => 'nullable|string|max:1000',

            // Residential rent fields
            'adults'     => $isResidentialRent ? 'required|integer|min:1|max:20' : 'nullable|integer',
            'children'   => $isResidentialRent ? 'required|integer|min:0|max:20' : 'nullable|integer',
            'has_pets'   => $isResidentialRent ? 'required|boolean' : 'nullable|boolean',
            'pet_details'=> 'nullable|string|max:255',

            // Commercial fields
            'intended_use'  => $isCommercial ? 'required|string|max:255' : 'nullable|string|max:255',
            'business_name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'adults.required'       => 'Please specify how many adults will be living in the property.',
            'children.required'     => 'Please specify the number of children (enter 0 if none).',
            'has_pets.required'     => 'Please indicate whether you have pets.',
            'intended_use.required' => 'Please describe the intended use of this commercial property.',
        ];
    }
}
