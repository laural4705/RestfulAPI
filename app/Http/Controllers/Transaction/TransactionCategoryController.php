<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\ApiController;
use App\Models\Transaction;

class TransactionCategoryController extends ApiController
{
    //We only need this method because we want to obtain a list of categories for a specific
    //transaction

    //This is a complex operation.  We obtain a single transaction using transactions/1 and then
    //we add /categories to the url.  Using the method below, the transaction will have a
    //product assigned to it and the product will have multiple categories assigned, these will
    //be returned.

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Transaction $transaction)
    {
        $categories = $transaction->product->categories;
        return $this->showAll($categories);
    }

}
