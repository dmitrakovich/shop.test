<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\Filterable as FilterableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property int $id
 * @property int|null $one_c_id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Brand>
 */
class Brand extends Model implements Auditable, Filterable
{
    use FilterableTrait;
    use AuditableTrait;

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = true;

    protected static function booted(): void
    {
        static::saved(function (self $brand): void {
            $brand->url()->updateOrCreate([], ['slug' => $brand->slug]);
        });
    }

    public static function elasticField(): ?string
    {
        return 'brand.id';
    }

    /**
     * Make default brand
     */
    public static function getDefault(): self
    {
        return self::make([
            'id' => 57,
            'name' => 'BAROCCO',
            'slug' => 'barocco',
        ]);
    }
}
