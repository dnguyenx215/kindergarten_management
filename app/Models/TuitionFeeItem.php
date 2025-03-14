<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionFeeItem extends Model
{
    protected $fillable = [
        'tuition_fee_id', 
        'fee_item_id', 
        'amount', 
        'quantity'
    ];

    public function tuitionFee()
    {
        return $this->belongsTo(TuitionFee::class);
    }

    public function feeItem()
    {
        return $this->belongsTo(FeeItem::class);
    }
}