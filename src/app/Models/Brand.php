<?php

namespace App\Models;

use App\Traits\AttributeFilterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use WendellAdriel\Lift\Attributes\Cast;
use WendellAdriel\Lift\Attributes\Column;
use WendellAdriel\Lift\Attributes\Fillable;
use WendellAdriel\Lift\Lift;

class Brand extends Model
{
    use AttributeFilterTrait;
    use Lift;

    #[Cast('int')]
    #[Column(default: 57)]
    public int $id;

    #[Fillable]
    #[Cast('int')]
    public int $one_c_id;

    #[Fillable]
    #[Column(default: 'BAROCCO')]
    public string $name;

    #[Fillable]
    #[Column(default: 'barocco')]
    public string $slug;

    #[Fillable]
    public string $seo;

    #[Fillable]
    #[Cast('datetime')]
    public ?Carbon $created_at;

    #[Fillable]
    #[Cast('datetime')]
    public ?Carbon $updated_at;

    protected static function getRelationColumn()
    {
        return 'brand_id';
    }

    /**
     * Make dafault brand
     */
    public static function getDefault(): self
    {
        return self::make(self::defaultValues());
    }
}
