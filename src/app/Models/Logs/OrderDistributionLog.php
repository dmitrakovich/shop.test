<?php

namespace App\Models\Logs;

use App\Admin\Models\Administrator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDistributionLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     */
    protected $table = 'log_order_distribution';

    /**
     * Get the admin associated with the action log.
     */
    public function admin()
    {
        return $this->belongsTo(Administrator::class, 'admin_user_id');
    }
}
