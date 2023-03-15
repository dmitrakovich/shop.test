<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPassport extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
