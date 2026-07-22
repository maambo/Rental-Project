<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'verification_tier_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'starts_at'    => 'datetime',
        'ends_at'      => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tier()
    {
        return $this->belongsTo(VerificationTier::class, 'verification_tier_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function propertyLimit(): int
    {
        return $this->tier?->property_limit ?? 1;
    }

    public function hasUnlimitedProperties(): bool
    {
        return $this->propertyLimit() === -1;
    }
}
