<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyApplication extends Model
{
    protected $fillable = [
        'user_id', 'property_id', 'status',
        'message', 'preferred_move_in', 'additional_comments',
        // Residential
        'adults', 'children', 'has_pets', 'pet_details',
        // Commercial
        'intended_use', 'business_name',
        // Applicant terms
        'applicant_terms',
        // Workflow timestamps
        'review_started_at', 'payment_requested_at', 'payment_deadline', 'completed_at',
        'landlord_agreed_terms_at', 'tenant_agreed_terms_at',
        'rejection_reason',
    ];

    protected $casts = [
        'preferred_move_in'    => 'date',
        'has_pets'             => 'boolean',
        'review_started_at'       => 'datetime',
        'payment_requested_at'    => 'datetime',
        'payment_deadline'        => 'datetime',
        'completed_at'            => 'datetime',
        'landlord_agreed_terms_at'=> 'datetime',
        'tenant_agreed_terms_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user()     { return $this->belongsTo(User::class); }
    public function property() { return $this->belongsTo(Property::class); }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopePending($q)          { return $q->where('status', 'pending'); }
    public function scopeUnderReview($q)      { return $q->where('status', 'under_review'); }
    public function scopePaymentRequested($q) { return $q->where('status', 'payment_requested'); }
    public function scopeCompleted($q)        { return $q->where('status', 'completed'); }
    public function scopeRejected($q)         { return $q->where('status', 'rejected'); }
    public function scopeCancelled($q)        { return $q->where('status', 'cancelled'); }

    /** Any status that means the process is still ongoing. */
    public function scopeActive($q)
    {
        return $q->whereIn('status', ['pending', 'under_review', 'payment_requested']);
    }

    /** Payment requested but deadline has passed. */
    public function scopePaymentExpired($q)
    {
        return $q->where('status', 'payment_requested')
                 ->whereNotNull('payment_deadline')
                 ->where('payment_deadline', '<', now());
    }

    public function isPaymentExpired(): bool
    {
        return $this->status === 'payment_requested'
            && $this->payment_deadline !== null
            && $this->payment_deadline->isPast();
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'under_review', 'payment_requested']);
    }

    public function isPaymentRequested(): bool
    {
        return $this->status === 'payment_requested';
    }

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
}
