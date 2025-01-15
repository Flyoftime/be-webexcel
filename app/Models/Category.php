<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Relasi dengan Subcategory
    public function subcategories()
    {
        return $this->hasMany(Subcategory::class);
    }

    // Relasi dengan Product
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
