<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerReview extends Model
{
    protected $fillable = [
        'job_booking_id', 'client_id', 'worker_profile_id',
        'rating', 'comment', 'worker_reply', 'replied_at',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'replied_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function booking()       { return $this->belongsTo(JobBooking::class, 'job_booking_id'); }
    public function client()        { return $this->belongsTo(User::class, 'client_id'); }
    public function workerProfile() { return $this->belongsTo(WorkerProfile::class); }

    // ── Hooks ────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function (WorkerReview $review) {
            $review->workerProfile->recalculateRating();
        });

        static::deleted(function (WorkerReview $review) {
            $review->workerProfile->recalculateRating();
        });
    }
}
