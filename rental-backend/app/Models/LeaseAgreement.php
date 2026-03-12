<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseAgreement extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'landlord_id',
        'status',
        'content',
        'monthly_rent',
        'start_date',
        'end_date',
        'tenant_signed_at',
        'landlord_signed_at',
        'approved_at',
    ];

    protected $casts = [
        'monthly_rent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'tenant_signed_at' => 'datetime',
        'landlord_signed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }
}
