<?php

namespace App\Models;

use App\Transformers\CategoryTransformer;
use App\Transformers\TransactionTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    //can be massively assigned (from create and update methods in controller)
    protected $fillable = [
        'name',
        'description',
    ];

    protected $hidden = [
        'pivot'
    ];

    public $transformer = CategoryTransformer::class;

    //BelongsToMany is used for many to many relationship
    public function products() {
        return $this->belongsToMany(Product::class);
    }
}
