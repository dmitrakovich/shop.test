<?php

namespace App\Models\User;

use App\Enums\Consent\ConsentFormEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $device_id
 * @property int|null $user_id
 * @property string|null $fio
 * @property string|null $phone
 * @property bool|null $cookie_analytics_enabled
 * @property bool|null $cookie_marketing_enabled
 * @property Carbon|null $personal_data_consent_recorded_at
 * @property bool|null $personal_data_consent
 * @property ConsentFormEnum|null $consent_request_source
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Device|null $device
 * @property-read User|null $user
 */
class DeviceConsent extends Model
{
    protected $fillable = [
        'device_id',
        'user_id',
        'fio',
        'phone',
        'cookie_analytics_enabled',
        'cookie_marketing_enabled',
        'personal_data_consent_recorded_at',
        'personal_data_consent',
        'consent_request_source',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'cookie_analytics_enabled' => 'boolean',
        'cookie_marketing_enabled' => 'boolean',
        'personal_data_consent_recorded_at' => 'datetime',
        'personal_data_consent' => 'boolean',
        'consent_request_source' => ConsentFormEnum::class,
    ];

    /**
     * @return BelongsTo<Device, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
