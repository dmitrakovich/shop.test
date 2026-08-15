<?php

namespace App\Models;

use App\Contracts\Filterable;
use App\Traits\Filterable as FilterableTrait;
use Database\Factories\FabricFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $seo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Url|null $url
 *
 * @implements Filterable<Fabric>
 */
class Fabric extends Model implements Filterable
{
    use FilterableTrait;

    /** @use HasFactory<FabricFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'seo',
    ];

    public static function elasticField(): ?string
    {
        return 'fabric_ids';
    }
}
