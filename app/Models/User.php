<?php

namespace App\Models;

use App\Transformers\UserTransformer;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    //Using strings here because we are going to use these values for comparisons
    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';

    const ADMIN_USER = 'true';
    const REGULAR_USER = 'false';

    public $transformer = UserTransformer::class;

    protected $table = 'users';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'verified', //2 possible values
        'verification_token',
        'admin', //2 possible values
    ];

    public function isVerified() {
        return $this->verified == User::VERIFIED_USER;
    }

    public function isAdmin() {
        return $this->admin == User::ADMIN_USER;
    }
    //This is a static method because we don't need any specific attribute or value from user
    //Use a long value to escape a brute force attack
    public static function generateVerificationCode() {
        return Str::random(40);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    //This is where we can hide variable in a JSON response
    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //Mutators
    public function setNameAttribute($name) {
        $this->attributes['name'] = Str::lower($name);
    }
    public function setEmailAttribute($email) {
        $this->attributes['email'] = Str::lower($email);
    }
    //Accessors
    public function getNameAttribute($name) {
        return Str::title($name);
    }
}
