<?php

namespace App\Admin\Models;

use Encore\Admin\Auth\Database\Administrator as AdministratorBase;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $user_last_name Фамилия
 * @property string|null $user_patronymic_name Отчество
 * @property string|null $trust_number Номер доверенности
 * @property \Illuminate\Support\Carbon|null $trust_date Дата доверенности
 * @property string $short_name
 * @property mixed $avatar
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Encore\Admin\Auth\Database\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\Encore\Admin\Auth\Database\Permission[] $permissions
 */
class Administrator extends AdministratorBase
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'trust_date' => 'datetime',
    ];

    /**
     * Get admin short name.
     */
    public function getShortNameAttribute(): string
    {
        return (!empty($this->user_last_name) ? ($this->user_last_name . ' ') : '') . (!empty($this->name) ? (ucfirst(mb_substr($this->name, 0, 1)) . '.') : '');
    }
}
