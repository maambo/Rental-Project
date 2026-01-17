<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationTier extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'price_display',
        'price_amount',
        'property_limit',
        'features',
        'styling',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'styling' => 'array',
        'is_active' => 'boolean',
    ];
}
