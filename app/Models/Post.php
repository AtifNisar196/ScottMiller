<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    use HasFactory;

    //scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    //relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}