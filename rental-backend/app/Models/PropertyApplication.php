<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyApplication extends Model
{
    protected $fillable = [
        'user_id',
        'property_id',
        'status',
        'message',
        'preferred_move_in',
        'additional_comments',
        // Residential rent
        'adults',
        'children',
        'has_pets',
        'pet_details',
        // Commercial
        'intended_use',
        'business_name',
    ];

    protected $casts = [
        'preferred_move_in' => 'date',
        'has_pets'          => 'boolean',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
