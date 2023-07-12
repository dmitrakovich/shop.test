<?php

namespace App\Models\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Token class.
 *
 * @property int $id
 * @property string $token
 * @property Carbon|null $expire_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder instagram() for instagram token
 * @method static void updateToken(string $newToken, ?Carbon $expireDate = null) update token in db
 */
class Token extends Model
{
    /**
     * Services ids
     */
    final const INSTAGRAM = 1;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expire_date' => 'datetime',
    ];

    /**
     * Convert the model to its string representation.
     */
    public function __toString()
    {
        return $this->token;
    }

    /**
     * Check if the token has expired
     */
    public function isExpired(): bool
    {
        return !empty($this->expire_date) && $this->expire_date->isPast();
    }

    /**
     * Update token in db
     */
    public function scopeUpdateToken(Builder $builder, string $newToken, Carbon $expireDate = null): void
    {
        $builder->update([
            'token' => $newToken,
            'expire_date' => $expireDate,
        ]);
    }

    /**
     * Query for instagram token
     */
    public function scopeInstagram(Builder $builder): Builder
    {
        return $builder->where('id', self::INSTAGRAM);
    }
}
