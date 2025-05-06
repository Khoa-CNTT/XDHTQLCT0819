<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Savingsgoal extends Model
{
    use HasFactory;
    protected $table = 'savingsgoals';
    protected $fillable =  [
        'user_id',
        'slug',
        'name',
        'target',
        'save_money',
        'start_day',
        'end_day',
        'savings_percentage',
        'save_money_today',
    ];
}
