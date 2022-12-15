<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Group
 *
 * @property int $id
 * @property string $name
 * @property float $discount
 */
class Group extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var  string
     */
    protected $table = 'user_groups';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'discount'];

    /**
     * Default model data
     */
    public static function defaultData(): array
    {
        return [
            'name' => 'unknown',
            'discount' => 0,
        ];
    }
}
