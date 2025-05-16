<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    use HasFactory;
    protected $table = 'stories';
    protected $fillable = [
        'user_id',
        'action'
    ];
    protected $hidden = ['user_id'];
}
