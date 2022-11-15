<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Jenssegers\Agent\Facades\Agent;

/**
 * Class Product
 *
 * @property string $id
 * @property int $user_id
 * @property int $cart_id
 * @property int $yandex_id
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
    final const DEVICE_ID_COOKIE_NAME = 'device_id';

    /**
     * @var string
     */
    final const YANDEX_ID_COOKIE_NAME = '_ym_uid';

    /**
     * @var string
     */
    final const GOOGLE_ID_COOKIE_NAME = '_ga';

    /**
     * @var int 1 year
     */
    final const COOKIE_LIFE_TIME = 525600;

    /**
     * @var array
     */
    final const TYPES = ['mobile', 'desktop'];

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
     */
    public static function generateId(Request $request): string
    {
        return md5(
            uniqid($request->getHost()).$request->ip()
        );
    }

    /**
     * Get exists device or make new
     */
    public static function getOrNew(): self
    {
        $id = Cookie::get(self::DEVICE_ID_COOKIE_NAME, self::getDefaultId());

        return self::firstOrNew(compact('id'));
    }

    /**
     * Generate default id for undefineds
     */
    protected static function getDefaultId(): string
    {
        return 'undefined_'.time();
    }

    /**
     * Return device id
     */
    public static function getId(): string
    {
        return Cookie::get(self::DEVICE_ID_COOKIE_NAME) ?? self::getDefaultId();
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
        });
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
     * Get the cart that owns the device.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Set the device's yandex id
     */
    public function setYandexId(?int $yandexId = null): void
    {
        $yandexId ??= (int) Cookie::get(self::YANDEX_ID_COOKIE_NAME);

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
            $googleId = Cookie::get(self::GOOGLE_ID_COOKIE_NAME);
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
}
