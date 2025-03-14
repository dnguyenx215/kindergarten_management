<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'receipt_number', 
        'student_id', 
        'tuition_fee_id', 
        'amount', 
        'payment_method', 
        'transaction_id', 
        'payment_details', 
        'payment_date', 
        'received_by'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function tuitionFee()
    {
        return $this->belongsTo(TuitionFee::class);
    }

    public function receivedByUser()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}