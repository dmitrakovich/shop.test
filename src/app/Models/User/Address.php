<?php

namespace App\Models\User;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
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
 * @property-read Country $country
 * @property-read User $user
 */
class Address extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_addresses';

    /**
     * Address country
     */
    public function country(): Relations\BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the user associated with this address.
     */
    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get address row.
     */
    public function getAddressRow()
    {
        $resultAddress = [];
        $resultAddress[] = $this?->zip;
        $resultAddress[] = $this?->district ? 'р-н ' . $this->district : null;
        $resultAddress[] = $this?->street ? 'ул. ' . $this->street : null;
        $resultAddress[] = $this?->house ? 'д. ' . $this->house : null;
        $resultAddress[] = $this?->corpus;
        $resultAddress[] = $this?->room ? 'кв. ' . $this->room : null;

        return implode(', ', array_filter($resultAddress, fn ($item) => $item));
    }
}
