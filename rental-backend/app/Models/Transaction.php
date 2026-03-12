<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'UID',
        'RequestID',
        'TransactionID',
        'UserID',
        'NRC',
        'TransactionDate',
        'Amount',
        'Name',
        'Type',
        'Hash',
        'Phone',
        'Status',
        'Error',
        'Data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
}
