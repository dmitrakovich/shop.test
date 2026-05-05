<?php

namespace Database\Factories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Size>
 */
class SizeFactory extends Factory
{
    /**
     * @var class-string<Size>
     */
    protected $model = Size::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->numerify('##.#'),
            'slug' => (string)Str::uuid(),
            'insole' => (string)fake()->randomFloat(1, 22, 30),
            'is_active' => true,
        ];
    }

    /**
     * Inactive size (excluded from typical filters).
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }
}
