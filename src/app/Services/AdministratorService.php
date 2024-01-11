<?php

namespace App\Services;

use App\Admin\Models\Administrator;
use Illuminate\Support\Collection;

class AdministratorService
{
    /**
     * Get the administrator list.
     */
    public function getAdministratorList(): Collection
    {
        return Administrator::select('name', 'id', 'user_last_name')->get()->pluck('short_name', 'id');
    }

    /**
     * Retrieve a list of administrator logins.
     *
     * @return Collection A collection of administrator logins, where the key is the username and the value is the short name.
     */
    public function getAdministratorLoginList(): Collection
    {
        return Administrator::select('name', 'username', 'user_last_name')->get()->pluck('short_name', 'username');
    }
}
