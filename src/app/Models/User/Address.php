<?php

namespace App\Models\User;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Address
 *
 * @property int $id
 * @property int $user_id
 * @property int $country_id
 * @property string $region
 * @property string $city
 * @property string $zip
 * @property string $address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'city',
        'address',
    ];

    /**
     * The table associated with the model.
     *
     * @var  string
     */
    protected $table = 'user_addresses';

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
