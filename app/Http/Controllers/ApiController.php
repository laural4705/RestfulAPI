<?php

//This is a base controller for all of our related API controllers.  This will allow our
//code to be in one place.  This controller will be extended from all related controllers.
//All shared traits will be available.

//We will create a trait, not controlled by artisan, we need to just create trait folder and file
//We are extending from the ApiController, so every method available here is available in all the other controllers.
//So the trait should be used here only and not in each controller separately.

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //Allows us to use all methods in the trait file
    use ApiResponser;
}
