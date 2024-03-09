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
     * Perform any actions required before the model boots.
     *
     * @return void
     */
    protected static function booting()
    {
        // check tunnel
    }
}
