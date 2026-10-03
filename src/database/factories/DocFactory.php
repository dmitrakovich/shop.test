<?php

namespace Database\Factories;

use App\Models\Doc;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doc>
 */
class DocFactory extends Factory
{
    /**
     * @var class-string<Doc>
     */
    protected $model = Doc::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'title' => fake()->sentence(3),
            'sort' => fake()->numberBetween(100, 900),
            'html' => '<p>' . fake()->paragraph() . '</p>',
        ];
    }
}
