<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;
        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    //We must use User here because a seller is a user who has transactions already, but here
    //we are allowing them to create products to sell and they may not have any transactions
    //yet
    public function store(Request $request, User $seller)
    {
        //allow us to create new instance of product associated with seller. This is why we
        //don't create the store method in products.
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image', //not a file, an image is required
        ]);
        $data = $request->all();
        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        //since the store('path') is calculated from filesystems, we changed it to default images we don't need
        //to add any further instruction
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);
        return $this->showOne($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $request->validate([
            'quantity' => 'integer|min:1',
            'status' => 'in:' . Product::AVAILABLE_PRODUCT . ',' . Product::UNAVAILABLE_PRODUCT,
            'image' => 'image'
        ]);

        //Verify if seller is owner of the product
        $this->checkSeller($seller, $product);

        //We use intersect method to ignore new or empty values
        $product->fill($request->only([
            'name',
            'description',
            'quantity'
        ]));

        if ($request->has('status')) {
            $product->status = $request->status;

            if ($product->isAvailable() && $product->categories()->count() == 0) {
                return $this->errorResponse('An active product must have at least one category.', 422);
            }
        }
        if ($request->hasFile('image')) {
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }
        if ($product->isClean()) {
            return $this->errorResponse('You need to specify a different value to update.', 422);
        }
        $product->save();
        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        //Validate that the seller is the owner of the product
        $this->checkSeller($seller, $product);
        //Remove image
        Storage::delete($product->image);
        //Remove product
        $product->delete();
        return $this->showOne($product);
    }

    //Method to verify if seller is owner of the product
    protected function checkSeller($seller, $product) {
        if ($seller->id != $product->seller_id)
        {
            throw new HttpException(422, 'The specified seller is not the actual seller of the product');
        }
    }
}
