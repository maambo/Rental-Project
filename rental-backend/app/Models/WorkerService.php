<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerService extends Model
{
    protected $fillable = [
        'worker_profile_id', 'service_name', 'description',
        'rate_type', 'base_rate', 'minimum_charge', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'base_rate'      => 'decimal:2',
        'minimum_charge' => 'decimal:2',
    ];

    public function workerProfile() { return $this->belongsTo(WorkerProfile::class); }

    public function bookings() { return $this->hasMany(JobBooking::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
