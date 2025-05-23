<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function sendPasswordResetNotification($token)
    {
        $url = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . $this->email;

        $this->notify(new ResetPassword($token, $url));
    }

    protected $fillable = [
        'id',
        'username',
        'email',
        'password',
        'phone',
        'monthly_income',
        'monthly_customer_spending',
        'avatar',
        'currency',
        'fullName',
        'address',
        'role',
        'isActived',
        'isBlocked',
        'status',
        'verify_token',
        'last_login'
    ];



    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
