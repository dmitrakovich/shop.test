<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $one_c_product_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property int|null $stock_id
 * @property string $sku
 * @property string|null $category_name
 * @property float $buy_price
 * @property float $sell_price
 * @property int $size_none
 * @property int $size_31
 * @property int $size_32
 * @property int $size_33
 * @property int $size_34
 * @property int $size_35
 * @property int $size_36
 * @property int $size_37
 * @property int $size_38
 * @property int $size_39
 * @property int $size_40
 * @property int $size_41
 * @property int $size_42
 * @property int $size_43
 * @property int $size_44
 * @property int $size_45
 * @property int $size_46
 * @property int $size_47
 * @property int $size_48
 *
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\Brand|null $brand
 * @property-read \App\Models\Stock|null $stock
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Size[] $sizes
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AvailableSizes extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Map of field names and theirs size ids
     */
    const FIELD_TO_SIZE_ID_MAP = [
        'size_none' => 1,
        'size_31' => 20,
        'size_32' => 21,
        'size_33' => 2,
        'size_34' => 3,
        'size_35' => 4,
        'size_36' => 5,
        'size_37' => 6,
        'size_38' => 7,
        'size_39' => 8,
        'size_40' => 9,
        'size_41' => 10,
        'size_42' => 22,
        'size_43' => 23,
        'size_44' => 24,
        'size_45' => 25,
        'size_46' => 26,
        'size_47' => 27,
        'size_48' => 28,
    ];

    /**
     * Get the product that owns the available size.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Get the category that owns the available product size.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand that owns the available product size.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the stock that owns the available size.
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get all of the media for the product.
     */
    public function media(): MorphMany
    {
        $mediaInstance = new Media();
        $query = $mediaInstance->newQuery();
        $table = $mediaInstance->getTable();

        return new MorphMany($query, new Product(), "$table.model_type", "$table.model_id", 'product_id');
    }

    /**
     * Get all sizes associated with the product.
     */
    public function sizes(): MorphToMany
    {
        return $this->morphedByMany(Size::class, 'attribute', 'product_attributes', 'product_id', 'attribute_id', 'product_id');
    }

    /**
     * Get the fallback media URL.
     */
    public function getFallbackMediaUrl(): string
    {
        return asset('/images/no-image-100.png');
    }

    /**
     * Remove records where sum all sizes = 0
     */
    public static function removeEmptySizes(): int
    {
        $sizeFieldsSum = implode(' + ', self::getSizeFields());

        return self::query()->whereRaw("$sizeFieldsSum = 0")->delete();
    }

    /**
     * Returns an array of size fields.
     * The array includes the "size_none" field and all size fields from "size_31" to "size_48".
     */
    public static function getSizeFields(): array
    {
        $sizeFields = ['size_none'];
        for ($i = 31; $i <= 48; $i++) {
            $sizeFields[] = 'size_' . $i;
        }

        return $sizeFields;
    }

    /**
     * Returns an array of size fields wrapped in the SUM function.
     *
     * The array includes the "size_none" field and all size fields, with each field name
     * preceded by the SUM function and followed by the original field name as an alias.
     */
    public static function getSumWrappedSizeFields(): array
    {
        return array_map(fn (string $size) => "SUM($size) as $size", self::getSizeFields());
    }

    /**
     * Returns an array of size fields wrapped in the GROUP_CONCAT function.
     *
     * The array includes the "size_none" field and all size fields, with each field name
     * preceded by the GROUP_CONCAT function and followed by the original field name as an alias.
     */
    public static function getGroupConcatWrappedSizeFields(): array
    {
        return array_map(fn (string $size) => "GROUP_CONCAT($size) as $size", self::getSizeFields());
    }

    /**
     * Converts a given field name to its corresponding Size ID.
     */
    public static function convertFieldToSizeId(string $field): int
    {
        return self::FIELD_TO_SIZE_ID_MAP[$field];
    }

    /**
     * Converts a given Size ID to its corresponding field name.
     */
    public static function convertSizeIdToField(int $sizeId): string
    {
        return array_search($sizeId, self::FIELD_TO_SIZE_ID_MAP);
    }

    /**
     * Get the size attributes for this model instance.
     */
    protected function getSizeAttributes(): array
    {
        return array_map('intval', $this->only($this->getSizeFields()));
    }

    /**
     * Get the available size attributes for this model instance.
     */
    protected function getAvailableSizeAttributes(): array
    {
        return array_filter($this->getSizeAttributes());
    }

    /**
     * Get the available size IDs for this model instance.
     */
    public function getAvailableSizeIds(): array
    {
        return array_map(
            fn ($field) => self::convertFieldToSizeId($field),
            array_keys($this->getAvailableSizeAttributes())
        );
    }

    /**
     * Returns a formatted string containing the available sizes for this model instance.
     */
    public function getFormattedSizes(): string
    {
        $sizes = [];
        foreach ($this->getAvailableSizeAttributes() as $sizeField => $count) {
            $sizes[] = $sizeField === 'size_none' ? 'без размера' : str_replace('size_', '', $sizeField);
        }

        return implode(', ', $sizes);
    }

    /**
     * Returns a formatted string containing the available sizes for specific stock.
     */
    public function getFormattedSizesForStock(string $stockField): ?string
    {
        $stockId = explode('_', $stockField)[1];
        $i = array_search($stockId, explode(',', $this->{$stockField}));

        if ($i === false) {
            return null;
        }
        $sizes = [];
        foreach ($this->only($this->getSizeFields()) as $sizeField => $counts) {
            if (!($count = explode(',', $counts)[$i])) {
                continue;
            }
            $size = $sizeField === 'size_none' ? 'б/р' : str_replace('size_', '', $sizeField);
            while ($count-- > 0) {
                $sizes[] = "<span class='stock-size'>$size</span>";
            }
        }

        return implode(' ', $sizes);
    }

    /**
     * Get the name for the stock.
     */
    public function getNameForStock(): string
    {
        $brandName = $this->brand->name ?? null;

        return "$brandName {$this->product_id} ({$this->sku})";
    }
}
