<?php

namespace Database\Factories;

use App\Models\Heel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Heel>
 */
class HeelFactory extends Factory
{
    /**
     * @var class-string<Heel>
     */
    protected $model = Heel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => (string)Str::uuid(),
            'seo' => fake()->optional()->sentence(),
        ];
    }
}
