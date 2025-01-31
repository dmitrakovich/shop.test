<?php

namespace App\Models\User;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $country_id
 * @property string|null $region Область/край
 * @property string|null $city Населенный пункт
 * @property string|null $zip Почтовый индекс
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $street Улица
 * @property string|null $house Дом
 * @property string|null $corpus Корпус
 * @property string|null $room Квартира
 * @property bool $approve Подтверждение о проверке
 * @property string|null $district Район
 *
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\User\User|null $user
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
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
        $resultAddress[] = $this->zip;
        $resultAddress[] = $this->district ? 'р-н ' . $this->district : null;
        $resultAddress[] = $this->street ? 'ул. ' . $this->street : null;
        $resultAddress[] = $this->house ? 'д. ' . $this->house : null;
        $resultAddress[] = $this->corpus;
        $resultAddress[] = $this->room ? 'кв. ' . $this->room : null;

        return implode(', ', array_filter($resultAddress, fn ($item) => $item));
    }
}
