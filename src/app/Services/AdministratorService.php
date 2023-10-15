<?php

namespace App\Services;

use App\Admin\Models\Administrator;
use \Illuminate\Support\Collection;

class AdministratorService
{

    /**
     * Get the administrator list.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAdministratorList(): Collection
    {
        return Administrator::select('name', 'id', 'user_last_name')->get()->pluck('short_name', 'id');
    }
}
