<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalHistory extends Model
{
    protected $fillable = [
        'property_id',
        'tenant_id',
        'landlord_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }
}
