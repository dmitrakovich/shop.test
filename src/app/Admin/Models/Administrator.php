<?php

namespace App\Admin\Models;

use Encore\Admin\Auth\Database\Administrator as AdministratorBase;

class Administrator extends AdministratorBase
{

    /**
     * Get admin short name.
     *
     * @return string
     */
    public function getShortNameAttribute(): string
    {
        return (!empty($this->user_last_name) ? ($this->user_last_name . ' ') : '')  . (!empty($this->name) ? (ucfirst(mb_substr($this->name, 0, 1)) . '.') : '');
    }
}
