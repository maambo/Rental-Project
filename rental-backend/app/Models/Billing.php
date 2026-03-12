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
    ];

    protected $casts = [
        'Date' => 'datetime',
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
