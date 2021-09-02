<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBuyerTransactionController extends ApiController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        //Make sure buyer is different from seller
        if ($buyer->id == $product->seller_id) {
            return $this->errorResponse('The buyer must be different from the seller',409);
        }
        if (!$buyer->isVerified()) {
            return $this->errorResponse('The buyer must be a verified user',409);
        }
        if (!$product->seller->isVerified()) {
            return $this->errorResponse('The seller must be a verified user',409);
        }
        if (!$product->isAvailable()) {
            return $this->errorResponse('The product is not available',409);
        }
        if ($product->quantity < $request->quantity) {
            return $this->errorResponse('This product does not have enough units for this transaction',409);
        }
        //make sure the transactions are performed sequentially so the data it is working off of hasn't been changed.
        return DB::transaction(function() use ($request, $product, $buyer) {
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id,
            ]);
            return $this->showOne($transaction,201);
        });
    }
}
