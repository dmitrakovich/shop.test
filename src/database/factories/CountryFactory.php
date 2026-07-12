<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * @var class-string<Country>
     */
    protected $model = Country::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->regexify('[A-Z]{2}');

        return [
            'name' => fake()->unique()->country(),
            'code' => $code,
            'mask' => '+375(__)___-__-__',
            'img' => '/images/icons/flags/' . strtolower($code) . '.svg',
            'prefix' => '+' . fake()->numberBetween(1, 999),
        ];
    }
}
