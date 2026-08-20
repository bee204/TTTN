<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'user_name',
        'name',
        'email',
        'role',
        'customer_id',
        'teacher_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = true;

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

}
