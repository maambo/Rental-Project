<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
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

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function hasRole(string $roleName): bool
    {
        return $this->roleModel && $this->roleModel->name === $roleName;
    }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isLandlord(): bool { return $this->hasRole('landlord'); }
    public function isTenant(): bool   { return $this->hasRole('tenant'); }
}
