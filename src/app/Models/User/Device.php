<?php

namespace App\Models\User;

use App\Contracts\ClientInterface;
use App\Enums\Cookie as CookieEnum;
use App\Enums\User\BanReason;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Jenssegers\Agent\Facades\Agent;
use Scriptixru\SypexGeo\SypexGeoFacade as SxGeo;

/**
 * @property int $id
 * @property string|null $web_id
 * @property string|null $api_id
 * @property int|null $user_id
 * @property int|null $yandex_id
 * @property string|null $google_id
 * @property string $type
 * @property string|null $ip_address
 * @property string|null $country_code
 * @property \Illuminate\Support\Carbon|null $banned_at
 * @property \App\Enums\User\BanReason|null $ban_reason
 * @property string $agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Models\Cart|null $cart
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Favorite[] $favorites
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Orders\Order[] $orders
 * @property-read \App\Models\User\User|null $user
 */
class Device extends Model implements ClientInterface
{
    /**
     * @var int 1 year
     */
    final const COOKIE_LIFE_TIME = 525600;

    /**
     * @var array
     */
    final const TYPES = ['mobile', 'desktop'];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'web_id',
        'api_id',
        'user_id',
        'yandex_id',
        'google_id',
        'type',
        'ip_address',
        'country_code',
        'agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'banned_at' => 'datetime',
        'ban_reason' => BanReason::class,
    ];

    /**
     * Generate new device web_id for new device
     */
    public static function generateNewWebId(Request $request): string
    {
        return md5(uniqid($request->getHost()) . $request->ip());
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function (self $device) {
            $device->setYandexId();
            $device->setGoogleId();
            $device->setType();
            $device->setAgent();
            $device->setIpAddress();
            $device->setCountryCode();
        });
    }

    /**
     * Device's cart
     */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    /**
     * Device's favorites
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Device's orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the user that owns the device.
     *
     * @todo need many to many relation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Set the device's yandex id
     */
    public function setYandexId(?int $yandexId = null): void
    {
        $yandexId ??= (int)Cookie::get(CookieEnum::YANDEX_ID->value);

        $this->attributes['yandex_id'] = $yandexId;
    }

    /**
     * Set the device's google id
     */
    public function setGoogleId(?string $googleId = null): void
    {
        if ($googleId) {
            $this->attributes['google_id'] = $googleId;
        } else {
            $googleId = Cookie::get(CookieEnum::GOOGLE_ID->value);
            $googleId = preg_replace("/^.+\.(.+?\..+?)$/", '\\1', $googleId);

            $this->attributes['google_id'] = $googleId;
        }
    }

    /**
     * Set the device's type
     */
    public function setType(?string $type = null): void
    {
        if ($type && in_array($type, self::TYPES)) {
            $this->attributes['type'] = $type;
        } else {
            $this->attributes['type'] = Agent::isDesktop() ? 'desktop' : 'mobile';
        }
    }

    /**
     * Set the device's user agent
     */
    public function setAgent(?string $agent = null): void
    {
        if ($agent) {
            $this->attributes['agent'] = $agent;
        } else {
            $browser = Agent::browser();
            $browserVersion = Agent::version($browser);

            $platform = Agent::platform();
            $platformVersion = Agent::version($platform);

            $this->attributes['agent'] = "$platform $platformVersion; $browser $browserVersion";
        }
    }

    /**
     * Set the device's ip address
     */
    public function setIpAddress(): void
    {
        $this->attributes['ip_address'] = request()->ip();
    }

    /**
     * Set the device's country code
     */
    public function setCountryCode(): void
    {
        $this->attributes['country_code'] = SxGeo::getCountry();
    }

    public function ban(BanReason $reason): void
    {
        // todo: tg log, в нем ссылка на страницу админки для разбана, при необходимости

        $this->forceFill([
            'banned_at' => now(),
            'ban_reason' => $reason,
        ])->save();
    }

    public function unban(): void
    {
        $this->forceFill(['banned_at' => null])->save();
    }

    public function isBanned(): bool
    {
        return !is_null($this->banned_at);
    }
}
