<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected  $table = 'categories';
    protected $fillable = [
        'user_id',
        'slug', 
        'name',
        'type',
        'icon'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
