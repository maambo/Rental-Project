<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobBooking extends Model
{
    protected $fillable = [
        'client_id', 'worker_profile_id', 'worker_service_id',
        'job_description', 'location', 'scheduled_date', 'scheduled_time',
        'agreed_price', 'platform_fee', 'worker_net',
        'status', 'client_notes', 'worker_notes', 'rejection_reason',
        'accepted_at', 'completed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'agreed_price'   => 'decimal:2',
        'platform_fee'   => 'decimal:2',
        'worker_net'     => 'decimal:2',
        'accepted_at'    => 'datetime',
        'completed_at'   => 'datetime',
    ];

    const STATUSES = [
        'pending', 'accepted', 'rejected',
        'in_progress', 'completed', 'cancelled', 'disputed',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function client()        { return $this->belongsTo(User::class, 'client_id'); }
    public function workerProfile() { return $this->belongsTo(WorkerProfile::class); }
    public function workerService() { return $this->belongsTo(WorkerService::class); }
    public function review()        { return $this->hasOne(WorkerReview::class); }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeActive($q)     { return $q->whereIn('status', ['accepted', 'in_progress']); }
    public function scopeCompleted($q)  { return $q->where('status', 'completed'); }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isReviewable(): bool
    {
        return $this->status === 'completed' && !$this->review()->exists();
    }

    public function calculateFees(float $agreedPrice, float $commissionRate = 0.08): void
    {
        $fee = round($agreedPrice * $commissionRate, 2);
        $this->update([
            'agreed_price' => $agreedPrice,
            'platform_fee' => $fee,
            'worker_net'   => $agreedPrice - $fee,
        ]);
    }
}
