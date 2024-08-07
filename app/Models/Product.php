<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = [
        'interior_url',
        'cover_url',
    ];

    //relationshipes
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id', 'id');
    }

    public function is_favourite()
    {
        return $this->hasOne(ProductFavourite::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function order_items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function bookmark_page()
    {
        return $this->hasOne(ProductBookmarkPage::class, 'product_id', 'id');
    }


    //scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
