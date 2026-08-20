<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'registration_id', 'attendance_date', 'status', 'note',
    ];

    protected $casts = [
        'attendance_date' => 'date:Y-m-d',
    ];

    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }
}
