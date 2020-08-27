<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
/**
 * Class User
 * 
 * @package App
 * 
 * @property-read string $fullName
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_name', 'first_name', 'patronymic_name', 'email', /*'password',*/ 'phone', 'birth_date', 'country', 'address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    /**
     * Аксессуар для поля, которе есть в БД
     *
     * @param string $valueFromDB
     * @return mixed
     */
    public function getFirstNameAttribute($valueFromDB)
    {
        return Str::ucfirst($valueFromDB);
    }
}
