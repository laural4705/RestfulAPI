<?php

namespace App\Models;

use App\Scopes\SellerScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//Extends from User instead of Model (default)
class Seller extends User
{
    use HasFactory;

    public static function boot() {
        parent::boot();
        static::addGlobalScope(new SellerScope);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}
