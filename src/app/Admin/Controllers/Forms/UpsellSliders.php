<?php

namespace App\Admin\Controllers\Forms;

use App\Enums\ProductCarouselEnum;
use App\Models\Ads\ProductCarousel;
use App\Models\Category;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class UpsellSliders extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Допродажи на финальной странице';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'слайдеры';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $finalUpsell = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_UPSELLS)->firstOrNew();
        $finalAccessories = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_ACCESSORIES)->firstOrNew();
        $finalSale = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_SALE)->firstOrNew();
        $requestData = $request->all();

        $finalUpsell->enum_type_id = ProductCarouselEnum::FINAL_UPSELLS;
        $finalUpsell->title = $requestData['final_upsell']['title'] ?? null;
        $finalUpsell->speed = $requestData['final_upsell']['speed'] ?? 3000;
        $finalUpsell->count = $requestData['final_upsell']['count'] ?? 12;
        $finalUpsell->sorting = $requestData['final_upsell']['sorting'] ?? 1;
        $finalUpsell->additional_settings = ['period' => $requestData['final_upsell']['additional_settings']['period'] ?? 90];
        $finalUpsell->save();

        $finalAccessories->enum_type_id = ProductCarouselEnum::FINAL_ACCESSORIES;
        $finalAccessories->title = $requestData['final_accessories']['title'] ?? null;
        $finalAccessories->speed = $requestData['final_accessories']['speed'] ?? 3000;
        $finalAccessories->count = $requestData['final_accessories']['count'] ?? 12;
        $finalAccessories->sorting = $requestData['final_accessories']['sorting'] ?? 2;
        $finalAccessories->categories_list = $requestData['final_accessories']['categories_list'] ?? [];
        $finalAccessories->additional_settings = [
            'additional_discount' => $requestData['final_accessories']['additional_settings']['additional_discount'] ?? 10,
            'discount_period' => $requestData['final_accessories']['additional_settings']['discount_period'] ?? 10,
        ];
        $finalAccessories->save();

        $finalSale->enum_type_id = ProductCarouselEnum::FINAL_SALE;
        $finalSale->title = $requestData['final_sale']['title'] ?? null;
        $finalSale->speed = $requestData['final_sale']['speed'] ?? 3000;
        $finalSale->count = $requestData['final_sale']['count'] ?? 12;
        $finalSale->sorting = $requestData['final_sale']['sorting'] ?? 3;
        $finalSale->categories_list = $requestData['final_sale']['categories_list'] ?? [];
        $finalSale->additional_settings = [
            'min_discount' => $requestData['final_sale']['additional_settings']['min_discount'] ?? 10,
        ];
        $finalSale->save();

        admin_success('Слайдеры допродаж успешно сохранены!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('С этим товаром также заказывают');
        $this->text('final_upsell.title', 'Заголовок');
        $this->number('final_upsell.speed', 'Скорость (мс)')->default(3000);
        $this->number('final_upsell.count', 'Количество выводимых товаров')->default(12);
        $this->number('final_upsell.sorting', 'Порядок сортировки')->default(1);
        $this->number('final_upsell.additional_settings.period', 'За какой период времени (дней)')->default(90);

        $this->divider('Аксессуары с доп. бонусом');
        $this->text('final_accessories.title', 'Заголовок');
        $this->number('final_accessories.speed', 'Скорость (мс)')->default(3000);
        $this->number('final_accessories.count', 'Количество выводимых товаров')->default(12);
        $this->number('final_accessories.sorting', 'Порядок сортировки')->default(2);
        $this->multipleSelect('final_accessories.categories_list', 'Категории')->options(Category::getFormatedTree());
        $this->number('final_accessories.additional_settings.additional_discount', 'Доп. скидка')->default(90);
        $this->number('final_accessories.additional_settings.discount_period', 'Период действия бонуса (часов)')->default(1);

        $this->divider('Товары на распродаже');
        $this->text('final_sale.title', 'Заголовок');
        $this->number('final_sale.speed', 'Скорость (мс)')->default(3000);
        $this->number('final_sale.count', 'Количество выводимых товаров')->default(12);
        $this->number('final_sale.sorting', 'Порядок сортировки')->default(3);
        $this->multipleSelect('final_sale.categories_list', 'Категории')->options(Category::getFormatedTree());
        $this->number('final_sale.additional_settings.min_discount', 'Минимальная скидка')->default(5);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        $finalUpsell = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_UPSELLS)->first(['title', 'speed', 'count', 'sorting', 'additional_settings']);
        $finalAccessories = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_ACCESSORIES)->first(['title', 'speed', 'count', 'sorting', 'categories', 'additional_settings']);
        $finalSale = ProductCarousel::where('enum_type_id', ProductCarouselEnum::FINAL_SALE)->first(['title', 'speed', 'count', 'sorting', 'categories', 'additional_settings']);

        return [
            'final_upsell' => $finalUpsell?->toArray(),
            'final_accessories' => $finalAccessories?->toArray(),
            'final_sale' => $finalSale?->toArray(),
        ];
    }
}
