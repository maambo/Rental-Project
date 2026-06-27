<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UtilityOption extends Model
{
    protected $fillable = ['utility_type_id', 'label', 'value', 'sort_order'];

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class);
    }
}
