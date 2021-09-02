<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Mockery\Exception\InvalidOrderException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
;

class Handler extends ExceptionHandler
{
    Use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        //Model/Data not found
        $this->renderable(function (ModelNotFoundException $exception, $request) {
            return $this->errorResponse('Model name does not exist', 404);
        });
        $this->renderable(function (NotFoundHttpException $exception, $request) {
            return $this->errorResponse('The specified URL cannot be found', 404);
        });
        //Authentication
        $this->renderable(function(AuthenticationException $exception, $request) {
            return $this->errorResponse('Unauthenticated', 422);
        });
        //Authorization - an authenticated user may not have permission to perform a task
        $this->renderable(function(AuthorizationException $exception, $request) {
            return $this->errorResponse($exception->getMessage(), 403);
        });
        //Route exists, but sent with wrong method
        $this->renderable(function (MethodNotAllowedHttpException $exception, $request) {
            return $this->errorResponse('This method is invalid', 405);
        });
        //General HTTP exception - handle general rule for any other exceptions
        $this->renderable(function (HttpException $exception, $request) {
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        });
        //Exceptions not related to HTTP but to database
        //Example, trying to remove a user (buyer or seller) that has a relation to a product
        //or transaction - violates foreign key constraint
        $this->renderable(function (QueryException $exception, $request) {
            $errorCode = $exception->errorInfo[1];
            if ($errorCode == 1451) {
                return $this->errorResponse('Cannot remove this resource, it is related to another resource', 409);
            }
        });
        $this->renderable(function (InvalidOrderException $exception, $request) {
            return response()->view('errors.invalid-order', [], 500);
        });
        //return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
    }
}
