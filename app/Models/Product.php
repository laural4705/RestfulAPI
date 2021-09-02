<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    const AVAILABLE_PRODUCT = 'available';
    const UNAVAILABLE_PRODUCT = 'unavailable';

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'status', //available or unavailable, so we will use constants and a method to check status
        'image',
        'seller_id', //foreign key, we will assign this
    ];

    protected $hidden = [
        'pivot'
    ];

    //This method will check to see if 'status' is equal to 'available', if yes, then true, else false.
    public function isAvailable() {
        return $this->status == Product::AVAILABLE_PRODUCT;
    }

    //BelongsToMany is used for many to many relationship
    public function categories() {
        return $this->belongsToMany(Category::class);
    }

    //If the model contains the foreign key (see above, seller_id), then it BelongsTo the other model, in
    //this case the product only belongsTo one seller
    public function seller() {
        return $this->belongsTo(Seller::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}
