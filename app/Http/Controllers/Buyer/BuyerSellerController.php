<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        //No direct relationship, must go through product (nested) with eager loading)
        //using pluck, must use product.seller because seller is nested under product
        //if we use unique('id') to remove duplicate sellers, then a blank will be left when the
        //seller is removed, so we use the value method to recreate the index of the collection
        //based on values returned.
        $sellers = $buyer->transactions()->with('product.seller')
            ->get()
            ->pluck('product.seller')
            ->unique('id')
            ->values();
        return $this->showAll($sellers);
    }
}
