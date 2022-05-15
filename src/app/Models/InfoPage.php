<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InfoPage extends Model
{
    use HasFactory;

    public static function getMenu()
    {
        return Cache::rememberForever('info-pages-menu',
            fn() => InfoPage::get(['slug', 'name', 'icon'])->toArray()
        );
    }
}
