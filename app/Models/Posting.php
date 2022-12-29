<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'id_posting', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'id_posting', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id_user');
    }
}
