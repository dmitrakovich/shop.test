<?php

namespace App\Models\Promo;

use App\Models\User\User;
use App\Models\User\UserPromocode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $sale_id
 * @property string $code
 * @property string|null $description
 * @property int|null $timer_sec
 * @property bool|null $activations_count
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Promo\Sale|null $sale
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\UserPromocode[] $userPromocodes
 * @property-read \App\Models\User\UserPromocode|null $userPromocode
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
     * Get the user promocode for the current authenticated user.
     */
    public function userPromocode(): HasOne
    {
        return $this->userPromocodes()->where('user_id', auth()->id())->one();
    }

    /**
     * Check if the promocode is expired for a specific user.
     */
    public function isExpiredForUser(): bool
    {
        return $this->userPromocode?->expired_at->lt(now()) ?: false;
    }

    /**
     * Get the sale for the user with the adjusted expiration date.
     */
    public function getSaleForUser(): ?Sale
    {
        if (!$this->sale || !$this->userPromocode) {
            return null;
        }
        $this->sale->end_datetime = $this->userPromocode->expired_at;

        return $this->sale;
    }
}
