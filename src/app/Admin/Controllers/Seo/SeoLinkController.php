<?php

namespace App\Admin\Controllers\Seo;

use App\Models\Seo\SeoLink;

use App\Enums\Seo\SeoLinkFolderEnum;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class SeoLinkController extends AdminController
{
    protected $title    = 'Seo ссылки';
    protected $states   = [
        'on'  => ['value' => 1, 'text' => 'Да', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => 'Нет', 'color' => 'danger'],
    ];

    protected function grid()
    {
        $grid = new Grid(new SeoLink);
        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->like('destination', 'Ссылка на сайте');
            $filter->in('folder_id', 'Папка')->multipleSelect(SeoLinkFolderEnum::list());
            $filter->disableIdFilter();
        });

        $grid->column('id',                   'ID')->sortable();
        $grid->column('seo_url',              'Seo ссылка');
        $grid->column('destination',          'Ссылка на сайте');
        $grid->column('tag',                  'Тег/Дата обновления');
        $grid->column('frequency',            'Частота')->editable()->sortable();
        $grid->column('frequency_updated_at', 'Дата обновления')->help("Дата/время обновления поля ''Частота''")->width(120)->sortable();
        $grid->column('h1',                   'h1');
        $grid->column('description',          'Описание')->display(function ($description) {
            return strlen($description) . ' символов';
        });
        $grid->column('meta_title',           'Meta заголовок');
        $grid->column('meta_description',     'Meta описание');
        $grid->column('meta_keywords',        'Ключевые слова');

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        $grid->paginate(50);
        $grid->perPages([25, 50, 100, 500]);
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableRowSelector();
        return $grid;
    }

    protected function form()
    {
        $form          = new Form(new SeoLink);

        $form->tab('Основное', function ($form) {
            $form->select('type_id',                 'Папка')->options(SeoLinkFolderEnum::list());
            $form->text('seo_url',                   'Seo ссылка')->placeholder('Введите seo ссылку')->creationRules(['required', 'unique:seo_links,seo_url'], [
                'required' => 'Поле обязательно для заполнения.',
                'unique'   => 'Такая ссылка уже существует.'
            ])->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
            $form->text('destination',               'Ссылка на сайте')->placeholder('Введите ссылку на сайте')->creationRules(['required'], [
                'required' => 'Поле обязательно для заполнения.',
            ])->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
            $form->text('tag',                       'Тег')->placeholder('Введите тег')->rules(['required'], ['required' => 'Поле обязательно для заполнения.']);
            $form->text('frequency',                 'Частота')->placeholder('Введите частоту')->default(0)->rules(['integer'], ['integer' => 'Поле частота должно быть числом.']);
            $form->text('h1',                        'H1 заголовок')->placeholder('Введите h1 заголовок');
            $form->ckeditor('description',           'Описание')->placeholder('Введите описание');
            $form->text('title',                     'Meta заголовок')->placeholder('Введите meta заголовок');
            $form->textarea('meta_description',      'Meta описание')->rows(2)->placeholder('Введите meta описание');
            $form->tags('meta_keywords',             'Мета-тег Keywords')->placeholder('Введите ключевые слова');
        });
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableReset();
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
