<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenancePlan extends Model
{
    protected $fillable = [
        'property_id',
        'title',
        'description',
        'scheduled_date',
        'scheduled_time',
        'status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
