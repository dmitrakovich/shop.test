<?php

namespace Database\Factories;

use App\Models\InfoPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InfoPage>
 */
class InfoPageFactory extends Factory
{
    /**
     * @var class-string<InfoPage>
     */
    protected $model = InfoPage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->sentence(3),
            'html' => '<p>' . fake()->paragraph() . '</p>',
        ];
    }
}
