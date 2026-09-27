<?php

namespace App\Models\Logs;

use App\Models\User\Device as UserDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $device_id
 * @property string $query Lowercased, whitespace-squished search text
 * @property int $results_count
 * @property string|null $filters_path
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @property-read UserDevice|null $device
 */
class SearchQueryLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'log_search_queries';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'device_id',
        'query',
        'results_count',
        'filters_path',
    ];

    /**
     * @return BelongsTo<UserDevice, $this>
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class);
    }
}
