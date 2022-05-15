<?php

namespace App\Admin\Controllers;

use App\Models\Sale;
use App\Models\Style;
use App\Models\Season;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Category;
use App\Models\Collection;
use Encore\Admin\Controllers\AdminController;

class SaleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sale';

    /**
     * Algorithms list
     */
    protected const ALGORITHMS_LIST = [
        Sale::ALGORITHM_FAKE => 'Ложная',
        Sale::ALGORITHM_SIMPLE => 'Простая',
        Sale::ALGORITHM_COUNT => 'От кол-ва',
        Sale::ALGORITHM_ASCENDING => 'По возрастанию'
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
        // $grid->column('categories', __('Categories'));
        // $grid->column('collections', __('Collections'));
        // $grid->column('styles', __('Styles'));
        // $grid->column('seasons', __('Seasons'));
        // $grid->column('only_new', __('Only new'));
        // $grid->column('add_client_sale', __('Add client sale'));
        // $grid->column('has_installment', __('Has installment'));
        // $grid->column('has_fitting', __('Has fitting'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

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
        $form = new Form(new Sale());

        $allCategoriesList = Category::getFormatedTree();
        $allCollectionsList = Collection::pluck('name','id')->toArray();
        $allStylesList = Style::orderBy('name')->pluck('name', 'id')->toArray();
        $allSeasonsList = Season::pluck('name','id')->toArray();

        $form->text('title', 'Название')->required();
        $form->text('label_text', 'Текст на шильде');
        $form->datetime('start_datetime', 'Дата начала')->default(date('Y-m-d H:i:s'));
        $form->datetime('end_datetime', 'Дата завершения')->default(date('Y-m-d 23:59:59'));
        $form->select('algorithm', 'Алгоритм')->options(self::ALGORITHMS_LIST)->default('simple');
        $form->text('sale', 'Скидка')->required();
        $form->listbox('categories', 'Категории')->options($allCategoriesList)->default(array_keys($allCategoriesList));
        $form->listbox('collections', 'Коллекции')->options($allCollectionsList)->default(array_keys($allCollectionsList));
        $form->listbox('styles', 'Стиль')->options($allStylesList)->default(array_keys($allStylesList));
        $form->listbox('seasons', 'Сезон')->options($allSeasonsList)->default(array_keys($allSeasonsList));
        $form->switch('only_new', 'Участвуют только новинки');
        $form->switch('add_client_sale', 'Клиентская скидка суммируется');
        $form->switch('has_installment', 'Действует рассрочка')->default(1);
        $form->switch('has_fitting', 'Действует примерка')->default(1);

        $form->saving(function (Form $form) use ($allCategoriesList, $allCollectionsList, $allStylesList, $allSeasonsList) {
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
