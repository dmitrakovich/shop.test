<?php

namespace Database\Factories;

use App\Models\TagGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagGroup>
 */
class TagGroupFactory extends Factory
{
    /**
     * @var class-string<TagGroup>
     */
    protected $model = TagGroup::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
