<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * class OrderAdminComment
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $comment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class OrderAdminComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment',
    ];

    /**
     * Format date for admin panel
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d.m.Y H:i:s');
    }
}
