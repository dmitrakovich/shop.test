<?php

namespace App\Admin\Controllers\Automation;

use App\Models\Config;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class InventoryBlacklistForm extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Список исключенных категорий';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Категории, которые не нужно синхронизировать с 1С';

    /**
     * Handle the form request.
     *
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        Config::find('inventory_blacklist')->update(['config' => [
            'categories' => $request->input('categories')['values'],
        ]]);

        admin_success('Список категорий сохранен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->list('categories', 'Категории');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return Config::findCacheable('inventory_blacklist');
    }
}
