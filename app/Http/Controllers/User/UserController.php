<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        //use this to return json so we can add extras to response, like response code.
        return $this->showAll($users);
        //return $users; This would return json, too, but we cannot add anything.
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => "required",
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        //provide a way to verify the account
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        //assign mass fields for user - array with all filled fields
        $user = User::create($data);

        //repeating this line with every method - will address in a later lesson
        //return user and the 201 response (creation successful)
        return $this->showOne($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //Implicit model binding - inject the instance of user instead of passing the id -
    public function show(User $user)
    {
        return $this->showOne($user);

    }

    public function update(Request $request, User $user) {
        $validate = $request->validate([
            //nothing required here because they may not want to update a field
            //validate e-mail but make sure to accept auth::users id
            'email' => 'email|unique:users,email,' .$user->id,
            'password' => 'min:6|confirmed',
            //a user can be modified to be an Admin, so there are 2 possible values, Admin
            //or User.
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,
            //This field cannot be directly modified, it can only be changed by the
            //verification code through e-mail
        ]);
        //Verify if we receive a name
        if ($request->has('name')) {
            $user->name = $request->name;
        }
        //Verify if we receive an email, if so, unverify and rerun verification code
        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }
        //Verify if we receive a password
        if ($request->has('password')) {
            $user->password = bcrypt($request->password);
        }
        //Verify if user in an admin, if the request has an admin
        if ($request->has('admin')) {
            if (!$user->isVerified()) {
                return $this->errorResponse('Only verified users can modify the admin field.',409);
            }
            $user->admin = $request->admin;
        }
        //Verify if something has changed
        if (!$user->isDirty()) {
            return $this->errorResponse('You need to specify a different value to update',422);
        }

        $user->save();
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->showOne($user);
    }

    public function verify($token) {
        $user = User::where('verification_token', $token)->firstOrFail();
        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->save();

        return $this->showMessage('The account has been verified successfully');
    }

    public function resend (User $user) {
        if ($user->isVerified()) {
            return $this->errorResponse('This user is already verified', 409);
        }
        retry(5, function () use($user) {
            Mail::to($user)->send(new UserCreated($user));
        },100);
        return $this->showMessage('The verification email has been resent.');
    }
}
