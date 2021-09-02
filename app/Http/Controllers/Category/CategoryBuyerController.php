<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\ApiController;
use App\Models\Category;

class CategoryBuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {
        $buyers = $category->products()
            ->whereHas('transactions') //only products with transactions
            ->with('transactions.buyer') //obtain the transactions and buyers
            ->get()
            ->pluck('transactions')//normally we could pluck('transactions.buyer') - too many collections so we need unique transactions (collapse)
            ->collapse()
            ->pluck('buyer')
            ->unique()
            ->values();
        return $this->showAll($buyers);
    }
}
