<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billing';

    protected $fillable = [
        'UserID',
        'Amount',
        'Date',
        'Description',
        'PackageName',
        'Approval',
        'Year',
        'lease_agreement_id',
        'status',
        'proof_of_payment',
        'paid_at',
    ];

    protected $casts = [
        'Date' => 'datetime',
        'paid_at' => 'datetime',
        'Amount' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }

    public function leaseAgreement()
    {
        return $this->belongsTo(LeaseAgreement::class);
    }
}
