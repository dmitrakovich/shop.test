<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string|null $passport_number Номер паспорта
 * @property string|null $series Серия паспорта
 * @property string|null $issued_by Кем выдан
 * @property \Illuminate\Support\Carbon|null $issued_date Когда выдан
 * @property string|null $personal_number Личный номер
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $registration_address Адрес прописки
 *
 * @property-read \App\Models\User\User|null $user
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class UserPassport extends Model
{
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'issued_date' => 'date',
    ];

    /**
     * user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
