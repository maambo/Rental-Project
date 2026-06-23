<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyApplication extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'status',
        'employer_name',
        'job_title',
        'monthly_income',
        'employment_duration',
        'reference_1_name',
        'reference_1_phone',
        'reference_1_relation',
        'reference_2_name',
        'reference_2_phone',
        'reference_2_relation',
        'preferred_move_in_date',
        'lease_duration_months',
        'occupants_count',
        'has_pets',
        'pet_details',
        'additional_comments',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
