<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * WorkSchedule class
 *
 * @property int $admin_user_id
 * @property string $date
 * @property \Illuminate\Support\Carbon created_at
 * @property \Illuminate\Support\Carbon updated_at
 */
class WorkSchedule extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
