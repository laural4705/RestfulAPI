<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;

class BuyerCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        //The relationship between product and category is many to many so it will return a collection with
        //other collections inside. We are plucking the categories within the products which is a collection
        //within a collection. We want one unique list of categories - use collapse method. Will create a
        //unique collection based on several collections.
        $categories = $buyer->transactions()->with('product.categories')
            ->get()
            ->pluck('product.categories')
            ->collapse()
            ->unique('id')
            ->values()
            ->sortBy('id');
        return $this->showAll($categories);
    }
}
