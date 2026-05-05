<?php

namespace Database\Factories;

use App\Models\ProductAttributes\Status;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Status>
 */
class StatusFactory extends Factory
{
    /**
     * @var class-string<Status>
     */
    protected $model = Status::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => (string)Str::uuid(),
        ];
    }
}
