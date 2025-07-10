<?php

namespace App\Models;

use App\Facades\Device;
use Illuminate\Database\Eloquent\Builder;
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Favorite forUser()
 */
class Favorite extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'device_id',
        'product_id',
    ];

    /**
     * Scope query to get favorites for the current user
     * If user is authenticated, filter by user_id
     * If user is not authenticated, filter by device_id
     */
    public function scopeForUser(Builder $query): void
    {
        $query->when(Auth::id(), function (Builder $query, int $userId) {
            $query->where('user_id', $userId);
        }, function (Builder $query) {
            $query->where('device_id', Device::id());
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
