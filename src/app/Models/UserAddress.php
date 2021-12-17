<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class UserAddress
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $country_id
 * @property string $region
 * @property string $city
 * @property string $zip
 * @property string $address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class UserAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'address',
    ];

    /**
     * Address country
     *
     * @return Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
