<?php

namespace App\Models\Api;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $expire_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Token updateToken(string $newToken, ?\Carbon\Carbon $expireDate = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Api\Token instagram()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
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
    public function scopeUpdateToken(Builder $builder, string $newToken, ?Carbon $expireDate = null): void
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
