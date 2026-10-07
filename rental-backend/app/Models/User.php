<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'google_id',
        'avatar',
        'phone',
        'id_type',
        'nrc_passport',
        'id_document_url',
        'selfie_url',
        'identity_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * `avatar` holds one of two things: an absolute URL when the account came in
     * through Google OAuth, or a relative disk path once the user uploads their
     * own image. Resolve both to something an <img src> can use, and fall back to
     * a generated initials avatar so the UI never renders a broken image.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http')
                ? $this->avatar
                : Storage::disk('public')->url($this->avatar);
        }

        return 'https://ui-avatars.com/api/?'.http_build_query([
            'name'       => $this->name,
            'background' => '374151',
            'color'      => 'fff',
            'size'       => 256,
        ]);
    }

    public function hasUploadedAvatar(): bool
    {
        return $this->avatar && ! str_starts_with($this->avatar, 'http');
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function landlordApplication()
    {
        return $this->hasOne(LandlordApplication::class);
    }

    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function reviews()
    {
        return $this->hasMany(PropertyReview::class);
    }

    public function tourRequests()
    {
        return $this->hasMany(TourRequest::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function tenantRentals()
    {
        return $this->hasMany(RentalHistory::class, 'tenant_id');
    }

    public function landlordRentals()
    {
        return $this->hasMany(RentalHistory::class, 'landlord_id');
    }

    public function savedProperties()
    {
        return $this->hasMany(SavedProperty::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)->active()->with('tier')->latestOfMany();
    }

    public function workerProfile()
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function jobBookingsAsClient()
    {
        return $this->hasMany(JobBooking::class, 'client_id');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->roleModel && $this->roleModel->name === $roleName;
    }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isLandlord(): bool { return $this->hasRole('landlord'); }
    public function isTenant(): bool   { return $this->hasRole('tenant'); }

    public function currentTier(): ?VerificationTier
    {
        return $this->activeSubscription?->tier
            ?? VerificationTier::where('name', 'starter')->where('tier_type', 'landlord')->first();
    }

    public function propertyLimit(): int
    {
        return $this->currentTier()?->property_limit ?? 1;
    }

    public function hasUnlimitedProperties(): bool
    {
        return $this->propertyLimit() === -1;
    }

    public function isIdentityVerified(): bool
    {
        return $this->identity_verified_at !== null;
    }
}
