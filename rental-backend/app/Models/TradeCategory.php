<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function workerProfiles()
    {
        return $this->hasMany(WorkerProfile::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
