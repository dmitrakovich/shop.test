<?php

namespace App\Models\OneC;

use Illuminate\Database\Eloquent\Model;

class OfflineOrder extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'sqlsrv';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SC6104';
}
