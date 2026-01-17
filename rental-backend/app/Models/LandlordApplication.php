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
        'verification_level',
        'landlord_type',
        'status',
        'id_document_url',
        'proof_of_address_url',
        'tax_certificate_url',
        'selfie_url',
        'video_walkthrough_url',
        'business_registration_url',
        'verification_submitted_at',
        'verification_approved_at',
        'payment_status',
        'subscription_expires_at',
        'listing_limit',
        'admin_notes',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'verification_submitted_at' => 'datetime',
        'verification_approved_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function isBasicVerified(): bool
    {
        return $this->status === 'approved' && $this->verification_level === 'basic';
    }

    public function isTrustedLandlord(): bool
    {
        return $this->status === 'approved' && $this->verification_level === 'trusted';
    }

    public function isPremiumVerified(): bool
    {
        return $this->status === 'approved' && $this->verification_level === 'premium';
    }

    public function getRemainingListingsCount(): int
    {
        $propertiesCount = Property::where('landlord_id', $this->user_id)->count();
        if ($this->listing_limit > 1000) {
            return 999999;
        }
        return max(0, $this->listing_limit - $propertiesCount);
    } // End of helper methods

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
