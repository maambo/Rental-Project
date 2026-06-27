<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
    protected $fillable = ['district_id', 'name', 'latitude', 'longitude'];

    protected $casts = [
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function district()
    {
        return $this->belongsTo(District::class)->with('province');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
