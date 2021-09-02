<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    //empty all tables before seeding
    public function run() {
        //Add this statement to clear the foreign keys when the tables truncate
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        User::truncate();
        Category::truncate();
        Product::truncate();
        Transaction::truncate();
        //Don't forget to truncate the pivot table - it does not have a model, we will
        //use the DB facade
        DB::table('category_product')->truncate();

        //We added an event listener to automatically send a verification e-mail when a user is created.  We
        //do not want that to happen each time we seed, so we use flushEventListeners() to keep that event from
        //occurring during seeding.  Best to do this for each model.

        User::flushEventListeners();
        Category::flushEventListeners();
        Product::flushEventListeners();
        Transaction::flushEventListeners();

        //Now add in the factories
        $usersQuantity =1000;
        $categoriesQuantity = 30;
        $productQuantity = 1000;
        $transactionQuantity = 1000;

        User::factory()->count($usersQuantity)->create();
        Category::factory()->count($categoriesQuantity)->create();

        //For each product we need to associate between 1 and 5 random categories
        Product::factory()->count($productQuantity)->create()->each(
            function ($product) {
                //this generates 1 to 5 ids of categories
                $categories = Category::all()->random(mt_rand(1, 5))->pluck('id');
                //attach the above ids to each category
                $product->categories()->attach($categories);
            });
        Transaction::factory()->count($transactionQuantity)->create();
    }
}
