<?php

namespace Database\Factories;

use App\Enums\Product\InventoryLogAction;
use App\Models\Logs\InventoryLog;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLog>
 */
class InventoryLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<InventoryLog>
     */
    protected $model = InventoryLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'action' => InventoryLogAction::Update,
            'added_sizes' => null,
            'removed_sizes' => null,
            'created_at' => now(),
        ];
    }

    public function restored(): static
    {
        return $this->state(['action' => InventoryLogAction::Restore]);
    }

    public function deleted(): static
    {
        return $this->state(['action' => InventoryLogAction::Delete]);
    }
}
