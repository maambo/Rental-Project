<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerPortfolioPhoto extends Model
{
    protected $fillable = ['worker_profile_id', 'image_url', 'caption', 'sort_order'];

    public function workerProfile() { return $this->belongsTo(WorkerProfile::class); }
}
