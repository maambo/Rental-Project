<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'code',
        'title',
        'description',
        'price',
        'street_address',
        'province_id',
        'district_id',
        'town_id',
        'latitude',
        'longitude',
        'property_type',
        'property_subtype',
        'listing_type',
        'bedrooms',
        'bathrooms',
        'square_feet',
        'amenities',
        'approval_status',
        'is_visible_in_search',
        'is_auto_suspended',
        'requires_real_time_photo',
        'real_time_photo_url',
        'submitted_date',
        'approved_date',
        'rejection_reason',
        'view_count',
        'like_count',
        'report_count',
    ];

    protected $casts = [
        'price'                    => 'decimal:2',
        'amenities'                => 'array',
        'is_visible_in_search'     => 'boolean',
        'is_auto_suspended'        => 'boolean',
        'requires_real_time_photo' => 'boolean',
        'latitude'                 => 'decimal:8',
        'longitude'                => 'decimal:8',
        'submitted_date'           => 'datetime',
        'approved_date'            => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($property) {
            if (!$property->code) {
                $property->code = self::generatePropertyCode();
            }
        });
    }

    private static function generatePropertyCode(): string
    {
        do {
            $code = 'PIV' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->exists());
        return $code;
    }

    // Relationships

    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function town()
    {
        return $this->belongsTo(Town::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(PropertyReview::class);
    }

    public function reports()
    {
        return $this->hasMany(PropertyReport::class);
    }

    public function tourRequests()
    {
        return $this->hasMany(TourRequest::class);
    }

    public function maintenancePlans()
    {
        return $this->hasMany(MaintenancePlan::class);
    }

    public function rentalHistories()
    {
        return $this->hasMany(RentalHistory::class);
    }

    public function viewingConfirmations()
    {
        return $this->hasMany(ViewingConfirmation::class);
    }

    public function applications()
    {
        return $this->hasMany(PropertyApplication::class);
    }

    public function utilities()
    {
        return $this->belongsToMany(UtilityType::class, 'property_utilities')
            ->withPivot('utility_option_id')
            ->withTimestamps();
    }

    // Scopes

    public function scopeVisibleInSearch($query)
    {
        return $query->where('is_visible_in_search', true)
                     ->where('is_auto_suspended', false)
                     ->where('approval_status', 'approved');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeForLandlord($query, int $landlordId)
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeSearch($query, array $filters)
    {
        return $query
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where(fn($q) => $q
                    ->where('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('street_address', 'like', "%{$s}%")
                )
            )
            ->when($filters['province_id'] ?? null,    fn($q, $v) => $q->where('province_id', $v))
            ->when($filters['district_id'] ?? null,    fn($q, $v) => $q->where('district_id', $v))
            ->when($filters['town_id'] ?? null,        fn($q, $v) => $q->where('town_id', $v))
            ->when($filters['property_type'] ?? null,  fn($q, $v) => $q->where('property_type', $v))
            ->when($filters['property_subtype'] ?? null, fn($q, $v) => $q->where('property_subtype', $v))
            ->when($filters['listing_type'] ?? null,   fn($q, $v) => $q->where('listing_type', $v))
            ->when($filters['min_price'] ?? null,      fn($q, $v) => $q->where('price', '>=', $v))
            ->when($filters['max_price'] ?? null,      fn($q, $v) => $q->where('price', '<=', $v))
            ->when($filters['bedrooms'] ?? null,       fn($q, $v) => $q->where('bedrooms', $v))
            ->when($filters['bathrooms'] ?? null,      fn($q, $v) => $q->where('bathrooms', $v))
            ->when($filters['amenities'] ?? null, function ($q, $amenities) {
                foreach (explode(',', $amenities) as $amenity) {
                    $q->whereJsonContains('amenities', trim($amenity));
                }
            });
    }

    // Helpers

    public function isResidential(): bool { return $this->property_type === 'residential'; }
    public function isCommercial(): bool  { return $this->property_type === 'commercial'; }
    public function isForRent(): bool     { return $this->listing_type === 'rent'; }
    public function isForSale(): bool     { return $this->listing_type === 'sale'; }
    public function isResidentialRent(): bool { return $this->isResidential() && $this->isForRent(); }

    // Accessors

    public function getPrimaryImageAttribute(): ?string
    {
        $primary = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
        return $primary?->image_url;
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->street_address,
            $this->town?->name,
            $this->district?->name,
            $this->province?->name,
        ]));
    }
}
