<?php

namespace App\Models\OneC;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'SC418';
}
