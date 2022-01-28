<?php

namespace App\Models;

use Illuminate\Http\Request;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Product
 *
 * @property string $id
 * @property integer $user_id
 * @property integer $cart_id
 * @property integer $yandex_id
 * @property string $google_id
 * @property string $type
 * @property string $agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 * @property-read Cart $cart
 */
class Device extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    const DEVICE_ID_COOKIE_NAME = 'device_id';

    /**
     * @var string
     */
    const YANDEX_ID_COOKIE_NAME = '_ym_uid';

    /**
     * @var string
     */
    const GOOGLE_ID_COOKIE_NAME = '_ga';

    /**
     * @var integer 1 year
     */
    const COOKIE_LIFE_TIME = 525600;

    /**
     * @var array
     */
    const TYPES = ['mobile', 'desktop'];

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'cart_id',
        'yandex_id',
        'google_id',
        'type',
        'agent',
    ];

    /**
     * Generate device id for new device
     *
     * @param Request $request
     * @return string
     */
    public static function generateId(Request $request): string
    {
        return md5(
            uniqid($request->getHost()) . $request->ip()
        );
    }

    /**
     * Get exists device or make new
     *
     * @return self
     */
    public static function getOrNew(): self
    {
        $id = Cookie::get(self::DEVICE_ID_COOKIE_NAME);

        return self::firstOrNew(compact('id'));
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::saving(function (self $device) {
            $device->setYandexIdAttribute();
            $device->setGoogleIdAttribute();
            $device->setTypeAttribute();
            $device->setAgentAttribute();
        });
    }

    /**
     * Get the user that owns the device.
     *
     * @todo need many to many relation
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart that owns the device.
     *
     * @return BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Set the device's yandex id
     *
     * @param integer|null $yandexId
     * @return void
     */
    public function setYandexIdAttribute(?int $yandexId = null): void
    {
        $yandexId = $yandexId ?? (int)Cookie::get(self::YANDEX_ID_COOKIE_NAME);

        $this->attributes['yandex_id'] = $yandexId;
    }

    /**
     * Set the device's google id
     *
     * @param string|null $googleId
     * @return void
     */
    public function setGoogleIdAttribute(?string $googleId = null): void
    {
        if ($googleId) {
            $this->attributes['google_id'] = $googleId;
        } else {
            $googleId = Cookie::get(self::GOOGLE_ID_COOKIE_NAME);
            $googleId = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", $googleId);

            $this->attributes['google_id'] = $googleId;
        }
    }

    /**
     * Set the device's type
     *
     * @param string|null $type
     * @return void
     */
    public function setTypeAttribute(?string $type = null): void
    {
        if ($type && in_array($type, self::TYPES)) {
            $this->attributes['type'] = $type;
        } else {
            $this->attributes['type'] = Agent::isDesktop() ? 'desktop' : 'mobile';
        }
    }

    /**
     * Set the device's user agent
     *
     * @param string|null $agent
     * @return void
     */
    public function setAgentAttribute(?string $agent = null): void
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
}
