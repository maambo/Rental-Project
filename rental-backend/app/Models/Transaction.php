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
        'user_id',
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

    /**
     * Typed relation via the new user_id FK. Prefer this over the legacy
     * string-based UserID lookup below wherever possible.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Legacy relation kept for backward compatibility with existing code that
     * reads Transaction::user — UserID is a plain string column with no FK,
     * so this only resolves correctly when UserID happens to be numeric.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserID');
    }
}
