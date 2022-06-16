<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ShortLink provide full link by short link
 *
 * @property integer $id
 * @property string $short_link
 * @property string $full_link
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ShortLink extends Model
{
    use HasFactory;
}
