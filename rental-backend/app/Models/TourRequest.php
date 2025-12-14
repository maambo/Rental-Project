<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourRequest extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'name',
        'email',
        'phone',
        'preferred_date',
        'preferred_time',
        'notes',
        'status',
        'landlord_response',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
