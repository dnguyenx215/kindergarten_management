<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeItem extends Model
{
    protected $fillable = [
        'name', 
        'amount', 
        'description', 
        'required', 
        'active'
    ];

    // Một khoản thu có thể thuộc nhiều phiếu thu
    public function tuitionFeeItems()
    {
        return $this->hasMany(TuitionFeeItem::class);
    }
}