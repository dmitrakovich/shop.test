<?php

namespace App\Admin\Controllers;

use App\Models\Ads\IndexLink;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class IndexLinkController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'IndexLink';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new IndexLink());

        $grid->column('title', 'Заголовок');
        $grid->column('links', 'Ссылки')->table(['text' => 'Текст', 'href' => 'Ссылка']);
        $grid->column('created_at', 'Дата создания');
        $grid->column('updated_at', 'Дата редактирования');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new IndexLink());

        $form->text('title', 'Заголовок')->required();
        $form->table('links', 'Ссылки', function ($table) {
            $table->text('text', 'Текст');
            $table->text('href', 'Ссылка');
        });

        return $form;
    }
}
