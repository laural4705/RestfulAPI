<?php

namespace App\Models;

use App\Scopes\BuyerScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;

//Extends from User instead of Model (default)
class Buyer extends User
{
    use HasFactory;

    public static function boot() {
        //call boot method of the parent class so we don't mess up base Laravel
        parent::boot();
        static::addGlobalScope(new BuyerScope);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
