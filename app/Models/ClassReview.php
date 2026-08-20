<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassReview extends Model
{
    protected $fillable = [
        'customer_id', 'class_id', 'rating', 'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function class()
    {
        return $this->belongsTo(YogaClass::class, 'class_id');
    }
}
