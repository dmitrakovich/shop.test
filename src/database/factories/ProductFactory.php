<?php

namespace Database\Factories;

use App\Enums\Product\ProductLabel;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Product>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->randomFloat(2, 1000, 30000);

        return [
            'one_c_id' => fake()->unique()->numberBetween(1, 999999999),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'sku' => fake()->unique()->bothify('SKU-######'),
            'label_id' => fake()->optional()->randomElement(ProductLabel::cases())?->value,
            'buy_price' => fake()->randomFloat(2, 500, $price),
            'price' => $price,
            'old_price' => fake()->boolean(30) ? fake()->randomFloat(2, $price, $price * 1.5) : 0,
            'category_id' => 0,
            'season_id' => 0,
            'brand_id' => 0,
            'manufacturer_id' => null,
            'collection_id' => 0,
            'color_txt' => fake()->optional()->safeColorName(),
            'fabric_top_txt' => fake()->optional()->word(),
            'fabric_inner_txt' => fake()->optional()->word(),
            'fabric_insole_txt' => fake()->optional()->word(),
            'fabric_outsole_txt' => fake()->optional()->word(),
            'heel_txt' => fake()->optional()->word(),
            'bootleg_height_txt' => fake()->optional()->word(),
            'description' => fake()->optional()->paragraph(),
            'action' => fake()->boolean(),
            'rating' => fake()->numberBetween(0, 100),
            'product_group_id' => null,
            'product_features' => fake()->optional()->sentence(3),
            'key_features' => fake()->optional()->sentence(3),
            'country_of_origin_id' => null,
        ];
    }

    /**
     * Mark the product as discounted.
     */
    public function discounted(): static
    {
        return $this->state(function (array $attributes): array {
            $price = (float) ($attributes['price'] ?? fake()->randomFloat(2, 1000, 30000));
            $oldPrice = max($price + 1, $price * 1.5);

            return [
                'price' => $price,
                'old_price' => fake()->randomFloat(2, $price + 1, $oldPrice),
            ];
        });
    }

    /**
     * Mark the product as published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'label_id' => null,
        ]);
    }

    /**
     * Mark the product as not published.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'label_id' => ProductLabel::DO_NOT_PUBLISH->value,
        ]);
    }
}
