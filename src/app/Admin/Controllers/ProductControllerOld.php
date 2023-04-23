<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Post\BatchRestore;
use App\Admin\Actions\Post\Restore;
use App\Admin\Actions\Product\AddToProductGroup;
use App\Admin\Actions\Product\RemoveFromProductGroup;
use App\Admin\Models\Media;
use App\Admin\Models\Product;
use App\Admin\Services\UploadImagesService;
use App\Enums\Product\ProductLabels;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\Manufacturer;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\Tag;
use Database\Seeders\ProductSeeder;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

class ProductControllerOld extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Товары';

    /**
     * костыльный объект, чтобы с сидера достать соответсвие id
     *
     * @var [type]
     */
    protected static $productSeederObject = null;

    public function __construct(private UploadImagesService $uploadImagesService)
    {
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', 'Id')->sortable();
        $grid->column('media', 'Фото')->display(fn ($pictures) => optional($this->getFirstMedia())->getUrl('thumb'))->image();
        $grid->column('deleted_at', 'Опубликован')->display(
            fn ($deleted) => !$deleted ? '<i class="fa fa-check text-green"></i>' : '<i class="fa fa-close text-red"></i>'
        )->sortable();
        $grid->column('slug', 'Slug');
        $grid->column('sku', 'Артикул');
        $grid->column('price', 'Цена')->sortable();
        $grid->column('old_price', 'Старая цена')->sortable();
        $grid->column('category.title', 'Категория');
        $grid->column('brand.name', 'Бренд');
        $grid->column('color_txt', 'Цвет');

        $grid->model()->orderBy('id', 'desc');
        $grid->model()->withTrashed();
        $grid->model()->with('media');
        $grid->paginate(30);

        $grid->actions(function ($actions) {
            $actions->add(new Restore());
        });
        $grid->batchActions(function ($batch) {
            $batch->add(new BatchRestore());
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter(); // Remove the default id filter
            $filter->where(function ($query) {
                $query->where('id', 'like', "%{$this->input}%")
                    ->orWhere('sku', 'like', "%{$this->input}%");
            }, 'Код товара / артикул');
        });

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
     * Restore product by id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(int $productId)
    {
        Product::withTrashed()->findOrFail($productId, ['id', 'label_id'])->restore();

        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        if ($form->isEditing()) {
            $product = Product::withTrashed()->find(request('product'));

            $form->tools(function (Form\Tools $tools) use ($product) {
                if ($product->trashed()) {
                    $tools->append('<div class="btn-group pull-right" style="margin-right: 5px">
                        <a href="' . route('admin.products.restore', $product->id) . '" class="btn btn-sm btn-success">
                        <i class="fa fa-history"></i>&nbsp;&nbsp;Восстановить</a></div>');
                    $tools->disableDelete();
                }
                $tools->disableView();
            });
        }

        $form->column(6, function ($form) {
            $form->html(function ($form) {
                if ($form->model()->trashed()) {
                    return '<h4 class="text-red">Товар удален</h4>';
                }
            });

            $uploadImagesService = $this->uploadImagesService;
            $form->html(fn ($form) => $uploadImagesService->show($form->model()->getMedia()))->setWidth(12, 0);
            $form->html($this->uploadImagesService->getImagesInput(), 'Картинки');

            $form->text('slug', __('Slug'))->default(Str::slug(request('slug')));
            $form->text('path', 'Путь')->disable();
            $form->text('sku', 'Артикул')->required()->default(request('title'));
            $form->currency('buy_price', 'Цена покупки')->symbol('BYN');
            $form->currency('price', 'Цена')->symbol('BYN')->required();
            $form->currency('old_price', 'Старая цена')->symbol('BYN');
        });
        $form->column(6, function ($form) {
            $form->multipleSelect('sizes', 'Размеры')->options(Size::pluck('name', 'id'))->default($this->getSizesIdFormRequest())->required();
            $form->multipleSelect('colors', 'Цвет для фильтра')->options(Color::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('fabrics', 'Материал для фильтра')->options(Fabric::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('styles', 'Стиль')->options(Style::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('heels', 'Тип каблука/подошвы')->options(Heel::pluck('name', 'id'));
            $form->select('category_id', 'Категория')->options(Category::getFormatedTree())->default($this->getCategoryIdFromRequeset())->required();
            $form->select('season_id', 'Сезон')->options(Season::pluck('name', 'id'))->required();
            $form->select('brand_id', 'Бренд')->options(Brand::orderBy('name')->pluck('name', 'id'))->required()->default(Brand::where('name', request('brand_name'))->value('id'));
            $form->select('collection_id', 'Коллекция')->options(Collection::pluck('name', 'id'))->required();
            $form->select('manufacturer_id', 'Производитель')->options(Manufacturer::pluck('name', 'id'));
            $form->text('color_txt', 'Цвет');
            $form->text('fabric_top_txt', 'Материал верха');
            $form->text('fabric_inner_txt', 'Материал внутри');
            $form->text('fabric_insole_txt', 'Материал стельки');
            $form->text('fabric_outsole_txt', 'Материал подошвы');
            $form->text('bootleg_height_txt', 'Высота голенища');
            $form->text('heel_txt', 'Высота каблука/подошвы');

            $form->divider();
            $form->select('label_id', 'Метка')->options(ProductLabels::list());
            $form->text('rating', 'Рейтинг')->disable();
            $form->multipleSelect('tags', 'Теги')->options(Tag::pluck('name', 'id'));
            $form->hidden('deleted_at', 'Дата снятия с наличия');
        });

        $form->column(12, function ($form) {
            $form->divider('Описание');
            $form->ckeditor('description', '');
        });

        if (isset($product)) {
            $form->column(12, function ($form) use ($product) {
                $form->divider('Группа товаров');
                $form->html($this->productGroupGrid($product));
            });
        }

        $form->saving(function (Form $form) {
            if (!$this->checkIfMediaAdded($form)) {
                return $this->mediaNotAddedError();
            }
            if (empty($form->slug)) {
                $form->slug = Str::slug(Brand::where('id', $form->brand_id)->value('name') . '-' . $form->sku);
            }
            if (is_null($form->manufacturer_id)) {
                $form->manufacturer_id = 0;
            }
            if ((int)$form->label_id === ProductLabels::DO_NOT_PUBLISH->value) {
                $form->deleted_at = now();
            }

            $existingProduct = $this->findExistingProduct($form);
            if ($existingProduct) {
                return $this->productExistsError($existingProduct->id);
            }
        });

        $form->saved(function (Form $form) {
            // delete
            $removeImagesId = $form->input('remove_images') ?? [];
            Media::whereIn('id', $removeImagesId)->delete();
            // Storage::delete('file.jpg'); // !!!

            // add
            $sorting = array_filter(explode('|', (string)$form->input('sorting')));
            $addImages = $form->input('add_images') ?? [];
            foreach ($addImages as $image) {
                $media = $form->model()
                    ->addMedia(storage_path("app/$image"))
                    ->toMediaCollection();

                $key = array_search("new-{$media->name}", $sorting);
                $sorting[$key] = $media->id;
            }
            if (!empty($sorting)) {
                Media::setNewOrder($sorting);
            }

            $form->model()->url()->delete();
            $form->model()->url()->create(['slug' => $form->slug]);

            if (App::environment('production')) {
                $this->sendToOldSite($form);
            }
        });

        return $form;
    }

    private function productGroupGrid(Product $product)
    {
        $grid = new Grid(new Product());
        $grid->model()->whereNotNull('product_group_id')->where('product_group_id', $product->product_group_id)->with(['category', 'brand'])->orderBy('id', 'desc');

        $grid->column('id', 'ID товара');
        $grid->column('media', 'Изображение')->display(function () {
            return '<img src="' . $this->getFirstMediaUrl() . '" loading="lazy" width="100px">';
        });
        $grid->column('sku', 'Артикул товара')->display(function () {
            return $this->sku ?? $this->title;
        });
        $grid->column('category.title', 'Категория')->display(function ($categoryTitle) use ($product) {
            $result = $categoryTitle;
            $result .= ($this->category_id != $product->category_id) ? '<br><span class="text-danger">Категории товаров в группе отличаются</span>' : '';

            return $result;
        });
        $grid->column('brand.name', 'Бренд')->display(function ($brandName) use ($product) {
            $result = $brandName;
            $result .= ($this->brand_id != $product->brand_id) ? '<br><span class="text-danger">Бренды товаров в группе отличаются</span>' : '';

            return $result;
        });

        $grid->tools(function (Grid\Tools $tools) use ($product) {
            if ($product->product_group_id) {
                $tools->append(new RemoveFromProductGroup($product->id, $product->product_group_id));
            } else {
                $tools->append(new AddToProductGroup($product->id));
            }
        });
        $grid->setActionClass(ContextMenuActions::class);
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->disableExport();
        $grid->disableColumnSelector();
        $grid->disableRowSelector();

        return $grid->render();
    }

    /**
     * Получить id категории из запроса
     *
     * @return int|null
     */
    protected function getCategoryIdFromRequeset()
    {
        if (empty($categoryName = request('category_name'))) {
            return null;
        }
        $removeWords = [
            'женская', 'женские', 'женский', // ...
        ];
        $categoryName = trim(str_replace($removeWords, '', $categoryName));

        $categories = DB::table('categories')
            ->where('title', 'like', "%$categoryName%")
            ->get(['id', 'title']);

        if (count($categories) == 1) {
            return $categories[0]->id;
        }
        foreach ($categories as $category) {
            if ($category->title == $categoryName) {
                return $category->id;
            }
        }

        return null;
    }

    /**
     * Получить id размеров из запроса
     */
    public function getSizesIdFormRequest(): ?array
    {
        if (empty($sizes = request('new_sizes'))) {
            return null;
        }
        $sizes = explode(';', (string)$sizes);

        return Size::whereIn('name', $sizes)->pluck('id')->toArray();
    }

    /**
     * Get olt attribute id from product seeder
     *
     * @param  string  $type
     * @param  int  $newId
     */
    protected function getOldId($type, $newId): int
    {
        $seeder = self::$productSeederObject ?? (self::$productSeederObject = new ProductSeeder);
        $oldIds = array_flip($seeder->attributesList[$type]['new_id'] ?? []);

        return $oldIds[$newId] ?? 0;
    }

    /**
     * Отправить товары на старый сайт
     *
     * @return void
     */
    protected function sendToOldSite(Form $form)
    {
        $data = [
            'product' => [
                'product_id' => $form->model()->id,
                'parent_id' => 0,
                'product_ean' => '',
                'product_quantity' => 0,
                'unlimited' => 0,
                'product_availability' => '',
                'product_date_added' => date('Y-m-d H:i:s'),
                'date_modify' => date('Y-m-d H:i:s'),
                'product_publish' => !$form->model()->trashed(),
                'product_tax_id' => 0,
                'currency_id' => 1,
                'product_template' => 'default',
                'product_url' => '',
                'product_old_price' => $form->old_price,
                'product_buy_price' => $form->buy_price,
                'product_price' => $form->price,
                'min_price' => $form->price,
                'different_prices' => 0,
                'product_weight' => 0,
                'image' => '',
                'product_manufacturer_id' => $this->getOldId('brand', $form->brand_id),
                'product_is_add_price' => 0,
                'add_price_unit_id' => 3,
                'average_rating' => 0,
                'reviews_count' => 0,
                'delivery_times_id' => 0,
                'hits' => 0,
                'weight_volume_units' => 0,
                'basic_price_unit_id' => 0,
                'label_id' => $form->label_id,
                'vendor_id' => 0,
                'access' => 1,
                'name_en-GB' => '',
                'alias_en-GB' => '',
                'short_description_en-GB' => '',
                'description_en-GB' => '',
                'meta_title_en-GB' => '',
                'meta_description_en-GB' => '',
                'meta_keyword_en-GB' => '',
                'name_ru-RU' => $form->sku,
                'alias_ru-RU' => $form->slug,
                'short_description_ru-RU' => '',
                'description_ru-RU' => $form->description ?? '',
                'meta_title_ru-RU' => '',
                'meta_description_ru-RU' => '',
                'meta_keyword_ru-RU' => '',
                'extra_field_1' => $form->color_txt ?? '',
                'extra_field_2' => $form->fabric_top_txt ?? '',
                'extra_field_3' => $form->collection_id,
                'extra_field_6' => 14,
                'extra_field_7' => $this->getOldId('season', $form->season_id),
                'extra_field_8' => $form->fabric_inner_txt ?? '',
                'extra_field_9' => $form->fabric_insole_txt ?? '',
                'extra_field_10' => $form->fabric_outsole_txt ?? '',
                'extra_field_11' => $form->heel_txt ?? '',
                'extra_field_12' => '',
                'extra_field_13' => implode(',', array_filter(array_map(fn ($value) => $this->getOldId('colors', $value), $form->colors))),
                'extra_field_14' => implode(',', array_filter(array_map(fn ($value) => $this->getOldId('fabrics', $value), $form->fabrics))),
                'extra_field_15' => implode(',', array_filter(array_map(fn ($value) => $this->getOldId('tags', $value), $form->tags))),
                'extra_field_16' => '',
                'extra_field_17' => '',
                'extra_field_18' => 0,
                'extra_field_21' => $form->manufacturer_id,
            ],
            'category' => [
                'product_id' => $form->model()->id,
                'category_id' => $this->getOldId('category', $form->category_id),
                'product_ordering' => 1,
            ],
            'sizes' => array_map(fn ($oldSizeId) => [
                'product_id' => $form->model()->id,
                'attr_id' => 2,
                'attr_value_id' => $oldSizeId,
                'price_mod' => '+',
                'addprice ' => 0,
            ], array_filter(array_map(fn ($size) => $this->getOldId('sizes', $size), $form->sizes))),

            'images' => $form->model()->getMedia()->map(fn ($image) => $image->getUrl('full'))->toArray(),

            'videos' => $form->model()->getMedia()->map(fn ($image) => $image->getCustomProperty('video'))->filter()->toArray(),

            'imidj' => $form->model()->getMedia()->map(fn ($image) => $image->getCustomProperty('is_imidj'))->filter()->toArray(),
        ];
        // dd($data);

        $data = [
            'token' => 'vTnD57Pdq45lkU',
            'data' => $data,
        ];

        // Log::info($data);
        $response = Http::asForm()->post('https://modny.by/saveimg_gRf5lP46jRm8s.php', $data);
        admin_info('Modny.by:', $response->body());
    }

    /**
     * Checks if photos have been added to the product.
     */
    protected function checkIfMediaAdded(Form $form): bool
    {
        $existing = count($form->model()->getMedia());
        $added = count($form->input('add_images') ?? []);
        $removed = count($form->input('remove_images') ?? []);

        return ($existing + $added - $removed) > 0;
    }

    /**
     * Returns a redirect response with an error message indicating that no photos have been added to the product.
     */
    protected function mediaNotAddedError(): RedirectResponse
    {
        $error = new MessageBag([
            'title' => 'Не добавлены фото товара!',
            'message' => 'Добавьте фото к товару.',
        ]);

        return back()->with(compact('error'))->withInput();
    }

    /**
     * Finds an existing product with the same slug as the one being edited/created.
     */
    protected function findExistingProduct(Form $form): ?Product
    {
        return Product::withTrashed()
            ->where('slug', $form->slug)
            ->when($form->isEditing(), function ($query) use ($form) {
                $query->where('id', '!=', $form->model()->id);
            })
            ->first(['id']);
    }

    /**
     * Returns a redirect response with an error message indicating that a product with the same name already exists.
     */
    protected function productExistsError(int $productId): RedirectResponse
    {
        $editLink = route('admin.products.edit', $productId);
        $error = new MessageBag([
            'title' => 'Товар с таким названием есть',
            'message' => '<a href="' . $editLink . '">Cсылка на редактирование этого товара<a>',
        ]);

        return back()->with(compact('error'));
    }
}
