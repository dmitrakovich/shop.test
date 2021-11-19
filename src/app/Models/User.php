<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
/**
 * Class User
 *
 * @package App
 *
 * @property-read string $fullName
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'usergroup_id',
        'first_name',
        'patronymic_name',
        'last_name',
        'phone',
        'email',
        'birth_date',
        'created_at',
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
     * User addresses
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Get fisrt user address if exist
     *
     * @return \App\Models\UserAddress
     */
    public function getFirstAddress()
    {
        return optional($this->addresses[0] ?? null);
    }

    /**
     * Check user has addresses
     *
     * @return boolean
     */
    public function hasAddresses(): bool
    {
        return !empty($this->getFirstAddress()->id);
    }

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
    /**
     * Farmat date in admin panel
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
