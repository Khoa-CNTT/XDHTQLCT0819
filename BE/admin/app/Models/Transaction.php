<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'amount',
        'transaction_date',
        'transaction_type',
        'type',
        'address',
        'description'
    ];
}
