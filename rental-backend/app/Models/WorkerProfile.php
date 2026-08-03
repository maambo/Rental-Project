<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id', 'trade_category_id', 'town_id',
        'tagline', 'bio', 'experience_years',
        'profile_photo_url', 'nrc_url', 'certificate_url',
        'is_verified', 'verified_at', 'verified_by',
        'service_radius_km', 'phone',
        'rating_average', 'rating_count',
        'is_active', 'is_featured',
    ];

    protected $casts = [
        'is_verified'    => 'boolean',
        'is_active'      => 'boolean',
        'is_featured'    => 'boolean',
        'verified_at'    => 'datetime',
        'rating_average' => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function user()         { return $this->belongsTo(User::class); }
    public function category()     { return $this->belongsTo(TradeCategory::class, 'trade_category_id'); }
    public function town()         { return $this->belongsTo(Town::class); }
    public function verifiedBy()   { return $this->belongsTo(User::class, 'verified_by'); }

    public function services()
    {
        return $this->hasMany(WorkerService::class)->where('is_active', true)->orderBy('service_name');
    }

    public function allServices()
    {
        return $this->hasMany(WorkerService::class)->orderBy('service_name');
    }

    public function portfolioPhotos()
    {
        return $this->hasMany(WorkerPortfolioPhoto::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(JobBooking::class);
    }

    public function reviews()
    {
        return $this->hasMany(WorkerReview::class)->latest();
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeActive($q)    { return $q->where('is_active', true); }
    public function scopeVerified($q)  { return $q->where('is_verified', true); }
    public function scopeFeatured($q)  { return $q->where('is_featured', true); }

    /**
     * Profiles that may be shown and booked in the marketplace: active, verified,
     * and — critically — still pointing at a trade category the admin hasn't
     * retired. Without the category check, retiring a skill would leave those
     * workers fully bookable under a skill that no longer exists.
     */
    public function scopeBookable($q)
    {
        return $q->active()
            ->verified()
            ->whereHas('category', fn ($c) => $c->where('is_active', true));
    }

    public function scopeInCategory($q, int $categoryId)
    {
        return $q->where('trade_category_id', $categoryId);
    }

    public function scopeSearch($q, ?string $term)
    {
        return $q->when($term, fn ($q, $t) =>
            $q->where(fn ($q) =>
                $q->where('tagline', 'like', "%{$t}%")
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$t}%"))
                  ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$t}%"))
            )
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /** True when the admin has retired the trade category this profile uses. */
    public function hasRetiredCategory(): bool
    {
        return ! ($this->category?->is_active ?? false);
    }

    public function recalculateRating(): void
    {
        $avg   = $this->reviews()->avg('rating') ?? 0;
        $count = $this->reviews()->count();
        $this->update(['rating_average' => round($avg, 2), 'rating_count' => $count]);
    }
}
