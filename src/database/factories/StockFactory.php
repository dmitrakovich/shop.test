<?php

namespace Database\Factories;

use App\Enums\StockType;
use App\Models\City;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
{
    /**
     * @var class-string<Stock>
     */
    protected $model = Stock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'one_c_id' => fake()->unique()->numberBetween(1, 999_999),
            'city_id' => City::factory(),
            'type' => StockType::SHOP,
            'name' => mb_substr($name, 0, 50),
            'internal_name' => mb_substr($name, 0, 50),
            'address' => fake()->streetAddress(),
            'check_availability' => false,
            'is_active' => true,
            'has_pickup' => false,
            'sorting' => 0,
            'site_sorting' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }

    public function stock(): static
    {
        return $this->state(fn (): array => [
            'type' => StockType::STOCK,
        ]);
    }
}
