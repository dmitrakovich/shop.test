<?php

namespace App\Models\Payments;

use App\Enums\Payment\OnlinePaymentStatusEnum;

use App\Models\Payments\OnlinePaymentStatus;

use Encore\Admin\Facades\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\{Model, Relations};

class OnlinePayment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            $model->statuses()->create([
                'admin_user_id'          => Admin::user()->id ?? null,
                'payment_status_enum_id' => OnlinePaymentStatusEnum::PENDING,
            ]);
        });
    }

    /**
     * Payment statuses
     *
     * @return Relations\HasMany
     */
    public function statuses(): Relations\HasMany
    {
        return $this->hasMany(OnlinePaymentStatus::class);
    }
}
