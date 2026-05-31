<?php

namespace App\Models;

use App\Enums\Product\RatingFactor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $views_coefficient
 * @property int $carts_coefficient
 * @property int $purchases_coefficient
 * @property int $price_coefficient
 * @property int $discount_coefficient
 * @property int $category_up_coefficient
 * @property int $category_down_coefficient
 * @property int $season_coefficient
 * @property int $created_at_coefficient
 * @property int $product_up_coefficient
 * @property int $product_down_coefficient
 * @property list<int>|null $category_up_ids
 * @property list<int>|null $category_down_ids
 * @property list<int>|null $product_up_ids
 * @property list<int>|null $product_down_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RatingAlgorithm extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'views_coefficient',
        'carts_coefficient',
        'purchases_coefficient',
        'price_coefficient',
        'discount_coefficient',
        'category_up_coefficient',
        'category_down_coefficient',
        'season_coefficient',
        'created_at_coefficient',
        'product_up_coefficient',
        'product_down_coefficient',
        'category_up_ids',
        'category_down_ids',
        'product_up_ids',
        'product_down_ids',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'views_coefficient' => 'integer',
            'carts_coefficient' => 'integer',
            'purchases_coefficient' => 'integer',
            'price_coefficient' => 'integer',
            'discount_coefficient' => 'integer',
            'category_up_coefficient' => 'integer',
            'category_down_coefficient' => 'integer',
            'season_coefficient' => 'integer',
            'created_at_coefficient' => 'integer',
            'product_up_coefficient' => 'integer',
            'product_down_coefficient' => 'integer',
            'category_up_ids' => 'array',
            'category_down_ids' => 'array',
            'product_up_ids' => 'array',
            'product_down_ids' => 'array',
        ];
    }

    public function isUsedInRatingConfig(): bool
    {
        $config = Config::findCacheable('rating');

        $popularityAlgorithmId = (int)($config['popularity_algorithm_id'] ?? 0);
        $newnessAlgorithmId = (int)($config['newness_algorithm_id'] ?? 0);

        return $this->id === $popularityAlgorithmId || $this->id === $newnessAlgorithmId;
    }

    /**
     * @return list<int>
     */
    public function categoryUpIds(): array
    {
        return $this->normalizeIds($this->category_up_ids);
    }

    /**
     * @return list<int>
     */
    public function categoryDownIds(): array
    {
        return $this->normalizeIds($this->category_down_ids);
    }

    /**
     * @return list<int>
     */
    public function productUpIds(): array
    {
        return $this->normalizeIds($this->product_up_ids);
    }

    /**
     * @return list<int>
     */
    public function productDownIds(): array
    {
        return $this->normalizeIds($this->product_down_ids);
    }

    /**
     * @return list<string>
     */
    public static function coefficientColumns(): array
    {
        return array_map(
            fn (RatingFactor $factor): string => $factor->coefficientColumn(),
            RatingFactor::cases()
        );
    }

    public function coefficientFor(RatingFactor $factor): int
    {
        $column = $factor->coefficientColumn();

        return (int)$this->{$column};
    }

    /**
     * @return list<int>
     */
    private function normalizeIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($value, 'is_numeric'))));
    }
}
