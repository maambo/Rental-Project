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
        'location',
        'province',
        'town',
        'bedrooms',
        'bathrooms',
        'sqft',
        'square_feet',
        'amenities',
        'is_approved',
        'approval_status',
        'submitted_date',
        'approved_date',
        'rejection_reason',
        'view_count',
        'like_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_approved' => 'boolean',
        'amenities' => 'array',
        'submitted_date' => 'datetime',
        'approved_date' => 'datetime',
    ];

    /**
     * Boot method to generate property code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($property) {
            if (!$property->code) {
                $property->code = self::generatePropertyCode();
            }
        });
    }

    /**
     * Generate a unique property code in format PIV####
     */
    private static function generatePropertyCode()
    {
        do {
            $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $code = 'PIV' . $number;
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Relationships
     */
    public function landlord()
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->orderBy('order');
    }

    public function reviews()
    {
        return $this->hasMany(PropertyReview::class);
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

    /**
     * Accessors
     */
    public function getPrimaryImageAttribute()
    {
        $primaryImage = $this->images()->where('is_primary', true)->first();
        return $primaryImage ? $primaryImage->image_url : ($this->images->first()->image_url ?? null);
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }
}
