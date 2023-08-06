<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Sale;
use App\Models\Season;
use App\Models\Style;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class SaleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Акции';

    /**
     * Algorithms list
     */
    protected const ALGORITHMS_LIST = [
        Sale::ALGORITHM_FAKE => 'Ложная',
        Sale::ALGORITHM_SIMPLE => 'Простая',
        Sale::ALGORITHM_COUNT => 'От кол-ва',
        Sale::ALGORITHM_ASCENDING => 'По возрастанию',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Sale());

        $grid->column('id', __('Id'));
        $grid->column('title', 'Название');
        $grid->column('label_text', 'Текст на шильде');
        $grid->column('start_datetime', 'Дата начала');
        $grid->column('end_datetime', 'Дата завершения');
        $grid->column('algorithm', 'Алгоритм')->using(self::ALGORITHMS_LIST);
        $grid->column('sale', 'Скидка');

        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
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
        $form = new Form(new Sale());

        $allCategoriesList = Category::getFormatedTree();
        $allCollectionsList = Collection::pluck('name', 'id')->toArray();
        $allStylesList = Style::orderBy('name')->pluck('name', 'id')->toArray();
        $allSeasonsList = Season::pluck('name', 'id')->toArray();

        $form->text('title', 'Название')->required();
        $form->text('label_text', 'Текст на шильде');
        $form->datetime('start_datetime', 'Дата начала')->default(date('d.m.Y H:i:s'))->format('DD.MM.YYYY HH:mm:ss');
        $form->datetime('end_datetime', 'Дата завершения')->default(date('d.m.Y 23:59:59'))->format('DD.MM.YYYY HH:mm:ss');
        $form->select('algorithm', 'Алгоритм')->options(self::ALGORITHMS_LIST)->default('simple');
        $form->text('sale', 'Скидка')->required();
        $form->listbox('categories', 'Категории')->options($allCategoriesList)->default(array_keys($allCategoriesList));
        $form->listbox('collections', 'Коллекции')->options($allCollectionsList)->default(array_keys($allCollectionsList));
        $form->listbox('styles', 'Стиль')->options($allStylesList)->default(array_keys($allStylesList));
        $form->listbox('seasons', 'Сезон')->options($allSeasonsList)->default(array_keys($allSeasonsList));
        $form->switch('only_new', 'Участвуют только новинки');
        $form->switch('only_discount', 'Участвуют только скидки');
        $form->switch('add_client_sale', 'Клиентская скидка суммируется');
        $form->switch('add_review_sale', 'Суммируется со скидкой за отзывы')->default(true);
        $form->switch('has_installment', 'Действует рассрочка')->default(1);
        $form->switch('has_fitting', 'Действует примерка')->default(1);

        $form->saving(function (Form $form) use ($allCategoriesList, $allCollectionsList, $allStylesList, $allSeasonsList) {
            if ($form->only_new === 'on' && $form->only_discount === 'on') {
                return back()->with('error', new MessageBag([
                    'title' => 'взаимоисключающие условия только новинки и только скидки'
                ]));
            }
            $form->categories = $this->prepareIdList($form->categories, $allCategoriesList);
            $form->collections = $this->prepareIdList($form->collections, $allCollectionsList);
            $form->styles = $this->prepareIdList($form->styles, $allStylesList);
            $form->seasons = $this->prepareIdList($form->seasons, $allSeasonsList);
        });

        // hotfix
        $form->saved(function (Form $form) {
            $form->model()->categories = empty($form->model()->categories) ? null : $form->model()->categories;
            $form->model()->collections = empty($form->model()->collections) ? null : $form->model()->collections;
            $form->model()->styles = empty($form->model()->styles) ? null : $form->model()->styles;
            $form->model()->seasons = empty($form->model()->seasons) ? null : $form->model()->seasons;
            $form->model()->save();
        });

        $form->tools(function (Form\Tools $tools) {
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

    /**
     * Prepare to save id list
     *
     * @return array
     */
    protected function prepareIdList(array $ids, array $allEntities)
    {
        $ids = array_filter($ids);
        if (count($ids) == count($allEntities)) {
            return null;
        }

        return array_map('intval', $ids);
    }
}
