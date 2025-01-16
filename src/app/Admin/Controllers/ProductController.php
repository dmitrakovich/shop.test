<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Product as ProductActions;
use App\Admin\Models\Media;
use App\Admin\Models\Product;
use App\Admin\Services\UploadImagesService;
use App\Enums\Product\ProductLabels;
use App\Events\Products\ProductCreated;
use App\Events\Products\ProductUpdated;
use App\Models\AvailableSizes;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Fabric;
use App\Models\Heel;
use App\Models\ProductAttributes\CountryOfOrigin;
use App\Models\ProductAttributes\Manufacturer;
use App\Models\Season;
use App\Models\Size;
use App\Models\Style;
use App\Models\TagGroup;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Displayers\ContextMenuActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

/**
 * @mixin Product
 * @phpstan-require-extends Product
 */
class ProductController extends AbstractAdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Товары';

    /**
     * List of status filters
     */
    const statusfilters = [
        'not_in_stock' => 'нет в наличии',
        'do_not_publish' => 'не публиковать',
        'do_not_update' => 'не обновлять',
        'liquidation' => 'ликвидация',
        'hit' => 'хит',
    ];

    /**
     * ProductController constructor
     */
    public function __construct(private UploadImagesService $uploadImagesService) {}

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', 'Id')->sortable();
        $grid->column('media', 'Фото')->display(fn () => $this->getFirstMediaUrl('default', 'thumb'))->image();
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
            $actions->add(new ProductActions\Restore());
        });
        $grid->batchActions(function ($batch) {
            $batch->add(new ProductActions\BatchRestore());
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter(); // Remove the default id filter
            $filter->where(function ($query) {
                $query->where('id', 'like', "%{$this->input}%")
                    ->orWhere('sku', 'like', "%{$this->input}%");
            }, 'Код товара / артикул');
            $filter->where($this->getStatusFilter(), 'Статус', 'status')->checkbox(self::statusfilters);
            $filter->in('brand_id', 'Бренд')->multipleSelect(Brand::pluck('name', 'id'));
            $filter->where($this->getCategoryFilter(), 'Категория', 'category')->multipleSelect(Category::getFormatedTree());
            $filter->where($this->getSizeCountFilter(), 'Количество размеров', 'size_count')->checkbox([5 => '5 и более размеров']);
        });

        return $grid;
    }

    /**
     * Adds a filter for statuses.
     */
    private function getStatusFilter(): \Closure
    {
        return function (Builder $query) {
            foreach ($this->input as $status) {
                match ($status) {
                    'not_in_stock' => $query->onlyTrashed(),
                    'do_not_publish' => $query->where('label_id', ProductLabels::DO_NOT_PUBLISH->value),
                    'do_not_update' => $query->where('label_id', ProductLabels::DO_NOT_UPDATE->value),
                    'liquidation' => $query->where('label_id', ProductLabels::LIQUIDATION->value),
                    'hit' => $query->where('label_id', ProductLabels::HIT->value),
                };
            }
        };
    }

    /**
     * Adds a filter for categories.
     */
    private function getCategoryFilter(): \Closure
    {
        return function (Builder $query) {
            $categories = [];
            foreach ($this->input ?? [] as $categoryId) {
                $categories = array_merge($categories, Category::getChildrenCategoriesIdsList($categoryId));
            }

            return $query->whereIn('category_id', $categories);
        };
    }

    /**
     * Adds a size count filter.
     */
    private function getSizeCountFilter(): \Closure
    {
        return function (Builder $query) {
            $productIds = DB::table('product_attributes')
                ->select('product_id', DB::raw('COUNT(*) as size_count'))
                ->where('attribute_type', Size::class)
                ->groupBy('product_id')
                ->having('size_count', '>=', 5)
                ->pluck('product_id');

            return $query->whereIn('id', $productIds->toArray());
        };
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
        $product = null;
        $productFromStock = $this->getStockProduct();

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

        $form->column(6, function (Form $form) use ($productFromStock) {
            $form->html(function ($form) {
                if ($form->model()->trashed()) {
                    return '<h4 class="text-red">Товар удален</h4>';
                }
            });

            $uploadImagesService = $this->uploadImagesService;
            $form->html(fn ($form) => $uploadImagesService->show($form->model()->getMedia()))->setWidth(12, 0);
            $form->html($this->uploadImagesService->getImagesInput(), 'Картинки');
            if ($form->isCreating()) {
                $form->hidden('slug', __('Slug'))->default('temp_slug_' . time());
                $form->hidden('old_slug', 'Old slug')->default('temp_old_slug_' . time());
            } else {
                $form->text('slug', __('Slug'))->disable();
            }
            $form->text('path', 'Путь')->disable();
            $form->text('sku', 'Артикул')->required()->default($productFromStock->sku);
            $form->currency('buy_price', 'Цена покупки')->symbol('BYN');
            $form->currency('price', 'Цена')->symbol('BYN')->required();
            $form->currency('old_price', 'Старая цена')->symbol('BYN');
        });
        $form->column(6, function (Form $form) use ($product, $productFromStock) {
            $form->multipleSelect('sizes', 'Размеры')->options(Size::pluck('name', 'id'))->default($productFromStock->getAvailableSizeIds())->required();
            $form->multipleSelect('colors', 'Цвет для фильтра')->options(Color::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('fabrics', 'Материал для фильтра')->options(Fabric::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('styles', 'Стиль')->options(Style::orderBy('name')->pluck('name', 'id'));
            $form->multipleSelect('heels', 'Тип каблука/подошвы')->options(Heel::pluck('name', 'id'));
            $form->select('category_id', 'Категория')->options(Category::getFormatedTree())->default($productFromStock->category_id)->required();
            $form->select('season_id', 'Сезон')->options(Season::pluck('name', 'id'))->required();
            $form->select('brand_id', 'Бренд')->options(Brand::orderBy('name')->pluck('name', 'id'))->required()->default($productFromStock->brand_id);
            $form->select('collection_id', 'Коллекция')->options(Collection::pluck('name', 'id'))->required();
            $form->select('manufacturer_id', 'Производитель')->options(Manufacturer::pluck('name', 'id'));
            $form->select('country_of_origin_id', 'Страна производитель')->options(CountryOfOrigin::pluck('name', 'id'));
            $form->text('color_txt', 'Цвет');
            $form->text('fabric_top_txt', 'Материал верха');
            $form->text('fabric_inner_txt', 'Материал внутри');
            $form->text('fabric_insole_txt', 'Материал стельки');
            $form->text('fabric_outsole_txt', 'Материал подошвы');
            $form->text('bootleg_height_txt', 'Высота голенища');
            $form->text('heel_txt', 'Высота каблука/подошвы');
            $form->text('key_features', 'Ключевая особенность');

            $form->divider();
            $form->select('label_id', 'Метка')->options(ProductLabels::list());
            $form->text('rating', 'Рейтинг')->disable();

            $form->checkbox('tags', 'Теги');
            $form->html(view('admin.product.tags', [
                'tagGroups' => TagGroup::with('tags')->get(),
                'productTags' => $product->tags ?? [],
            ]));
            $form->hidden('deleted_at', 'Дата снятия с наличия');
        });

        $form->column(12, function ($form) use ($product) {
            $form->divider('Описание');
            $form->html(view('admin.product.description-promt', ['product' => $product]));
            $form->ckeditor('description', '');
        });

        if ($product) {
            $form->column(12, function ($form) use ($product) {
                $form->divider('Группа товаров');
                $form->html($this->productGroupGrid($product));
            });
        }

        $form->saving(function (Form $form) {
            if (!$this->checkIfMediaAdded($form)) {
                return $this->mediaNotAddedError();
            }
            if ($form->isCreating()) {
                $form->old_slug = $this->generateOldSlug($form->brand_id, $form->sku);
            }
            if (is_null($form->manufacturer_id)) {
                $form->manufacturer_id = 0;
            }
            if (ProductLabels::DO_NOT_PUBLISH->value === (int)$form->label_id) {
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

            $mediaData = $form->input('mediaData');
            if (!empty($mediaData)) {
                $mediaList = Media::whereIn('id', array_keys($mediaData))->get();
                foreach ($mediaData as $mediaId => $mediaDataItem) {
                    $media = $mediaList->where('id', $mediaId)->first();
                    if (isset($mediaDataItem['media_video'])) {
                        $media->setCustomProperty('video', $mediaDataItem['media_video']);
                    } else {
                        $media->forgetCustomProperty('video');
                    }
                    if (isset($mediaDataItem['media_is_imidj'])) {
                        $media->setCustomProperty('is_imidj', (bool)$mediaDataItem['media_is_imidj']);
                    } else {
                        $media->forgetCustomProperty('is_imidj');
                    }
                    $media->save();
                }
            }

            if ($form->isCreating()) {
                event(new ProductCreated($form->model()->refresh()));
            }
            if ($form->isEditing()) {
                event(new ProductUpdated($form->model()));
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
                $tools->append(new ProductActions\RemoveFromProductGroup($product->id, $product->product_group_id));
            } else {
                $tools->append(new ProductActions\AddToProductGroup($product->id));
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
     * Generate a old slug based on brand name and SKU.
     */
    private function generateOldSlug(int $brandId, string $sku): string
    {
        return Str::slug(Brand::where('id', $brandId)->value('name') . '-' . $sku);
    }

    protected function getStockProduct(): AvailableSizes
    {
        if (empty($stockIds = request('stock_ids'))) {
            return new AvailableSizes();
        }

        return AvailableSizes::query()
            ->selectRaw(implode(', ', [
                'sku',
                'brand_id',
                'category_id',
                'MAX(buy_price) as buy_price',
                'MAX(sell_price) as sell_price',
                implode(', ', AvailableSizes::getSumWrappedSizeFields()),
            ]))
            ->groupBy(['sku', 'brand_id', 'category_id'])
            ->whereIn('id', explode(',', $stockIds))
            ->first();
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
