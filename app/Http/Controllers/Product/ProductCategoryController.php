<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\ApiController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $categories = $product->categories;
        return $this->showAll($categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product, Category $category)
    {
        //To interact with many to many relationships, use attach(), sync(), and syncWithoutDetaching()
        //attach() will add categories even if it duplicates them
        //sync() will add the category, if duplicated will remove the other ones
        //syncWithoutDetaching() -add new category without removing previous one, won't add dupes
        $product->categories()->syncWithoutDetaching([$category->id]);
        return $this->showAll($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category)
    {
        //Will remove records from the pivot table but not the Product or Category table.
        //Removed the relationship, not data
        //Be sure categories exist in the product list before removing
        if (!$product->categories()->find($category->id)) {
        return $this->errorResponse('The specified category is not a category of this product.', 404);
        }
        $product->categories()->detach($category->id);
        return $this->showAll($product->categories);
    }
}
