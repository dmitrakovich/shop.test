<?php

namespace App\Data\User;

use App\Data\Casts\ModelCast;
use App\Models\Country;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class UserAddressData extends Data
{
    #[MapInputName('country_id')]
    #[WithCast(ModelCast::class, modelClass: Country::class)]
    public Country $country;

    #[Max(50)]
    public string $city;

    #[Max(191)]
    public ?string $address;

    public function toArray(): array
    {
        return [
            'country_id' => $this->country->id,
            'city' => $this->city,
            'address' => $this->address,
        ];
    }
}
