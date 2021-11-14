<?php

namespace App\Models\Api;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApiUser extends Model
{
    use HasApiTokens, HasFactory;
}
