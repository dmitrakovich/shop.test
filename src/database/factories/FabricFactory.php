<?php

namespace Database\Factories;

use App\Models\Fabric;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Fabric>
 */
class FabricFactory extends Factory
{
    /**
     * @var class-string<Fabric>
     */
    protected $model = Fabric::class;

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
