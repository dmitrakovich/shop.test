<?php

namespace App\Models\Logs;

use App\Admin\Models\Administrator;
use App\Models\Orders\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $admin_id
 * @property string $action
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class OrderActionLog extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'log_order_actions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'admin_id',
        'action',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * Get the order associated with the action log.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin associated with the action log.
     */
    public function admin()
    {
        return $this->belongsTo(Administrator::class);
    }

    /**
     * Return order tracked fields for logging
     */
    public static function getTrackedFields(): array
    {
        return [
            'last_name' => 'Имя',
            'first_name' => 'Фамилия',
            'patronymic_name' => 'Отчество',
            'phone' => 'Телефон',
            'email' => 'Email',
            'country_id' => 'id страны',
            'region' => 'Область',
            'city' => 'Город',
            'zip' => 'ZIP',
            'user_addr' => 'Адрес',
            'weight' => 'Вес',
        ];
    }
}
