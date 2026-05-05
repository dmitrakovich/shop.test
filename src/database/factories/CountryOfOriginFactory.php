<?php

namespace Database\Factories;

use App\Models\ProductAttributes\CountryOfOrigin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CountryOfOrigin>
 */
class CountryOfOriginFactory extends Factory
{
    /**
     * @var class-string<CountryOfOrigin>
     */
    protected $model = CountryOfOrigin::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->country();

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . fake()->unique()->lexify('????')),
            'seo' => fake()->optional()->sentence(),
        ];
    }
}
