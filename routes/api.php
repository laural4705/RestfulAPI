<?php

use App\Http\Controllers\Buyer\BuyerCategoryController;
use App\Http\Controllers\Buyer\BuyerController;
use App\Http\Controllers\Buyer\BuyerProductController;
use App\Http\Controllers\Buyer\BuyerSellerController;
use App\Http\Controllers\Buyer\BuyerTransactionController;
use App\Http\Controllers\Category\CategoryBuyerController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Category\CategoryProductController;
use App\Http\Controllers\Category\CategorySellerController;
use App\Http\Controllers\Category\CategoryTransactionController;
use App\Http\Controllers\Product\ProductBuyerController;
use App\Http\Controllers\Product\ProductBuyerTransactionController;
use App\Http\Controllers\Product\ProductCategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductTransactionController;
use App\Http\Controllers\Seller\SellerBuyerController;
use App\Http\Controllers\Seller\SellerCategoryController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerProductController;
use App\Http\Controllers\Seller\SellerTransactionController;
use App\Http\Controllers\Transaction\TransactionCategoryController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Controllers\Transaction\TransactionSellerController;
use App\Http\Controllers\User\UserController;

    use Illuminate\Support\Facades\Route;

    //Buyers - this is a user, so should not be able to create, update or delete here - read only
    Route::resource('buyers', BuyerController::class, ['only' => ['index', 'show']]);
    Route::resource('buyers.transactions', BuyerTransactionController::class, ['only' => ['index']]);
    Route::resource('buyers.products', BuyerProductController::class, ['only' => ['index']]);
    Route::resource('buyers.sellers', BuyerSellerController::class, ['only' => ['index']]);
    Route::resource('buyers.categories', BuyerCategoryController::class, ['only' => ['index']]);

    //Categories - all resources except create and edit - we will use store and update, just not methods that return anything
    Route::resource('categories', CategoryController::class, ['except' => ['create', 'edit']]);
    Route::resource('categories.products', CategoryProductController::class, ['only' => ['index']]);
    Route::resource('categories.sellers', CategorySellerController::class, ['only' => ['index']]);
    Route::resource('categories.transactions', CategoryTransactionController::class, ['only' => ['index']]);
    Route::resource('categories.buyers', CategoryBuyerController::class, ['only' => ['index']]);

    //Products - read only
    Route::resource('products', ProductController::class, ['only' => ['index', 'show']]);
    Route::resource('products.transactions', ProductTransactionController::class, ['only' => ['index']]);
    Route::resource('products.buyers', ProductBuyerController::class, ['only' => ['index']]);
    Route::resource('products.categories', ProductCategoryController::class, ['only' => ['index', 'update', 'destroy']]);
    Route::resource('products.buyers.transactions', ProductBuyerTransactionController::class, ['only' => ['store']]);

    //Sellers - read only
    Route::resource('sellers', SellerController::class, ['only' => ['index', 'show']]);
    Route::resource('sellers.transactions', SellerTransactionController::class, ['only' => ['index']]);
    Route::resource('sellers.categories', SellerCategoryController::class, ['only' => ['index']]);
    Route::resource('sellers.buyers', SellerBuyerController::class, ['only' => ['index']]);
    Route::resource('sellers.products', SellerProductController::class, ['except' => ['create', 'edit', 'show']]);

    //Transactions - read only
    Route::resource('transactions', TransactionController::class, ['only' => ['index', 'show']]);
    Route::resource('transactions.categories', TransactionCategoryController::class, ['only' => ['index']]);
    //keeping sellers plural to keep consistency with other endpoints
    Route::resource('transactions.sellers', TransactionSellerController::class, ['only' => ['index']]);

    //Users - all methods allowed, but no return method necessary for create/edit
    Route::resource('users', UserController::class, ['except' => ['create', 'edit']]);
    //Route will receive a verification token - the route will execute an action in the UserController that will
    //search for that token match, if that user exists, modify the status to verify it and remove the token
    // to verify the user - send message stating account is verified.
    Route::name('verify')->get('users/verify/{token}', [UserController::class, 'verify']);
    //Resend the verification code if the user requests
    Route::name('resend')->get('users/{user}/resend', [UserController::class, 'resend']);

