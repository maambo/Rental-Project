<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordApplication extends Model
{
    protected $fillable = [
        'user_id',
        'tier',
        'status',
        'id_document_url',
        'proof_of_address_url',
        'tax_certificate_url',
        'admin_notes',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
