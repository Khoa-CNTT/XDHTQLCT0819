<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class NguoiDung extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'nguoidungs';

    protected $fillable = [
        'ten',
        'email',
        'password',
        'loai_goi',
        'tuychinh',
    ];

    protected $hidden = [
        'password',
    ];
}
