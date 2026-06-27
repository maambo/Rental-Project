<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordApplication extends Model
{
    protected $fillable = [
        'user_id',
        'nrc_passport',
        'address',
        'province',
        'town',
        'landlord_type',
        'status',
        'id_document_url',
        'proof_of_address_url',
        'tax_certificate_url',
        'selfie_url',
        'video_walkthrough_url',
        'business_registration_url',
        'verification_submitted_at',
        'admin_notes',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'verification_submitted_at' => 'datetime',
        'reviewed_at'               => 'datetime',
    ];

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Helpers

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['pending', 'rejected']);
    }
}
