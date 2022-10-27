<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Config class.
 *
 * @property string $key
 * @property array $config
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Config extends Model
{
    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'key';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'config'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = ['config' => 'array'];
}
