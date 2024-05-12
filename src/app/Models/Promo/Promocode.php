<?php

namespace App\Models\Promo;

use App\Models\User\User;
use App\Models\User\UserPromocode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $sale_id
 * @property string $code
 * @property string|null $description
 * @property int|null $timer_sec
 * @property int|null $activations_count
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Promo\Sale|null $sale
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\UserPromocode[] $userPromocodes
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Promocode extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    /**
     * Get the sale that owns the promocode.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the expiration date of the promocode.
     */
    public function getExpiredDate(): Carbon
    {
        $expiredAt = $this->sale->end_datetime;
        $lifeTime = $this->timer_sec ? now()->addSeconds($this->timer_sec) : null;

        if ($lifeTime && $lifeTime->lt($expiredAt)) {
            return $lifeTime;
        }

        return $expiredAt;
    }

    /**
     * Get the user promocodes associated with the promocode.
     */
    public function userPromocodes(): HasMany
    {
        return $this->hasMany(UserPromocode::class);
    }

    /**
     * Check if the promocode is expired for a specific user.
     */
    public function isExpiredForUser(User $user): bool
    {
        /** @var UserPromocode */
        $promocode = $this->userPromocodes()->firstWhere('user_id', $user->id);

        return $promocode ? $promocode->expired_at->lt(now()) : false;
    }
}
