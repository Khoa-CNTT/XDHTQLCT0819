<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurringtransaction extends Model
{
    use HasFactory;
    protected $table = 'recurringtransactions';

    protected $fillable = [
        'user_id',
        'category_id',
        'savingoal_id',
        'description',
        'amount',
        'period',
        'date',
    ];
}
