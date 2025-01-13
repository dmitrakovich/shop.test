<?php

namespace App\Models;

use App\Facades\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int|null $device_id
 * @property int|null $user_id
 * @property int $product_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Product|null $product
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Favorite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'product_id',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('for_user', function (Builder $builder) {
            if ($userId = Auth::id()) {
                $builder->where('user_id', $userId);
            } else {
                $builder->where('device_id', Device::id());
            }
        });
    }

    /**
     * Get the product associated with this favorite
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
