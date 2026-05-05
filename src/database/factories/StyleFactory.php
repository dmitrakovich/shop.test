<?php

namespace Database\Factories;

use App\Models\Style;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Style>
 */
class StyleFactory extends Factory
{
    /**
     * @var class-string<Style>
     */
    protected $model = Style::class;

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
