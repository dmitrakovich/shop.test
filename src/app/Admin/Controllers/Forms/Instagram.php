<?php

namespace App\Admin\Controllers\Forms;

use App\Services\InstagramService;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class Instagram extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Instagram';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'Блок instagram';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        // dd($request->all());

        (new InstagramService())->setTitle($request->input('title'));

        admin_success('Заголовок успешно сохранен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->ckeditor('title', 'Заголовок');
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return [
            'title' => (new InstagramService())->getTitle(),
        ];
    }
}
