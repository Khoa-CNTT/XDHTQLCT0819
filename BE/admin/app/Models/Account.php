<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'user_id',
        'name',
        'password',
        'type',
        'number_card',
        'expired',
        'pin_code'

    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
