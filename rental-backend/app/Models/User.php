<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'landlord_tier',
        'application_status',
        'approved_date',
        'property_count',
        'google_id',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_date' => 'datetime',
        ];
    }

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function landlordApplication()
    {
        return $this->hasOne(LandlordApplication::class);
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
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
}
