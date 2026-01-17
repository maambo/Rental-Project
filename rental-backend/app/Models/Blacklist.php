<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'nrc_passport',
        'phone_number',
        'email',
        'reason',
        'blacklisted_by',
        'type',
    ];

    public function blacklistedBy()
    {
        return $this->belongsTo(User::class, 'blacklisted_by');
    }
}
