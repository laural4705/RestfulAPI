<?php

namespace App\Providers;

use App\Mail\UserCreated;
use App\Mail\UserMailChanged;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Use this to avoid problems in migrations while using utf8mb4 - this is for the index
        Schema::defaultStringLength(191);

        //Event to send e-mail each time a new user is created
        //Retry function is used for failure-prone events.  It is likely a mail service could be down when
        //we are trying to send.  The retry method takes 3 parameters retry(# of times, function, time/milliseconds)
        User::created(function($user) {
            retry(5, function() use($user) {
                Mail::to($user)->send(new UserCreated($user));
            }, 100);
        });

        //Event to send updated e-mail only if the user e-mail has changed
        User::updated(function($user) {
            if($user->isDirty('email')) {
                retry(5, function () use ($user) {
                    Mail::to($user)->send(new UserMailChanged($user));
                }, 100);
            }
        });

        //Event to listen for updated for status of product (unavailable) when quantity is 0
        Product::updated(function($product) {
            if($product->quantity == 0 && $product->isAvailable()) {
                $product->status = Product::UNAVAILABLE_PRODUCT;
                $product->save();
            }
        });
    }
}
