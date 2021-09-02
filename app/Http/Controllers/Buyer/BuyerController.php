<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;

//All buyers are users, but not all users are buyers

//This model is a special case because the buyer is a user with at least one transaction
//Implicit model binding - if we add a buyer instance in the place of id and then try to return
//the user id of a user who is not a buyer, it will still be returned because the buyer is
//using the users table.  So we need to add a restriction - parts of the query that we need to
//add in a scope that will be executed each time a call for buyer is made.  We will create a
//scope directory for this. We need to create a global scope.  We also need to add that scope
//to the boot method in the model.

class BuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $buyers = Buyer::has('transactions')->get();
        return $this->showAll($buyers);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Buyer $buyer)
    {
        return $this->showOne($buyer);
    }

}
