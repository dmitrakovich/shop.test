<?php

namespace App\Data\User;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MergeValidationRules;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
#[MergeValidationRules]
class UserData extends Data
{
    #[Max(50)]
    public string $lastName;

    #[Max(50)]
    public string $firstName;

    #[Max(50)]
    public ?string $patronymicName;

    #[Email]
    #[Max(191)]
    public ?string $email;

    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public ?Carbon $birthDate;

    public UserAddressData $address;

    public static function rules(): array
    {
        return [
            'email' => [Rule::unique('users')->ignore(Auth::id())],
        ];
    }
}
