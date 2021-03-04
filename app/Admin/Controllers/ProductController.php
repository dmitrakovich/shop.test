<?php

namespace App\Admin\Controllers;

use App\Models\Tag;
use App\Models\Heel;
use App\Models\Size;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Style;
use App\Models\Fabric;
use App\Models\Season;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Product;
use App\Models\Category;
use App\Models\Collection;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';
    /**
     * костыльный объект, чтобы с сидера достать соответсвие id
     *
     * @var [type]
     */
    protected static $productSeederObject = null;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', __('Id'))->sortable();


        $grid->column('media', __('Фото'))->display(function ($pictures) {
            /*$media = [];
            foreach ($this->getMedia() as $image) {
                $media[] = $image->getUrl('thumb');
            }
            return $media;*/
            return optional($this->getFirstMedia())->getUrl('thumb');
        })->image(); // ->carousel();




        $grid->column('publish', __('Publish'))->bool()->sortable();
        $grid->column('slug', __('Slug'));
        $grid->column('title', __('Title'));
        // $grid->column('buy_price', __('Buy price'));
        $grid->column('price', __('Price'))->sortable();
        $grid->column('old_price', __('Old price'))->sortable();
        // $grid->column('category_id', __('Category id'));
        $grid->column('category.title', __('Категория'));
        // $grid->column('season_id', __('Season id'));
        $grid->column('season.name', __('Сезон'));
        // $grid->column('brand_id', __('Brand id'));
        $grid->column('brand.name', __('Бренд'));
        $grid->column('color_txt', __('Color txt'));
        $grid->column('fabric_top_txt', __('Fabric top txt'));
        $grid->column('fabric_inner_txt', __('Fabric inner txt'));
        $grid->column('fabric_insole_txt', __('Fabric insole txt'));
        $grid->column('fabric_outsole_txt', __('Fabric outsole txt'));
        $grid->column('heel_txt', __('Heel txt'));
        // $grid->column('description', __('Description'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

        $grid->model()->orderBy('id', 'desc');
        $grid->paginate(30);

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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('publish', __('Publish'));
        $show->field('slug', __('Slug'));
        $show->field('title', __('Title'));
        $show->field('buy_price', __('Buy price'));
        $show->field('price', __('Price'));
        $show->field('old_price', __('Old price'));
        $show->field('category_id', __('Category id'));
        $show->field('season_id', __('Season id'));
        $show->field('brand_id', __('Brand id'));
        $show->field('color_txt', __('Color txt'));
        $show->field('fabric_top_txt', __('Fabric top txt'));
        $show->field('fabric_inner_txt', __('Fabric inner txt'));
        $show->field('fabric_insole_txt', __('Fabric insole txt'));
        $show->field('fabric_outsole_txt', __('Fabric outsole txt'));
        $show->field('heel_txt', __('Heel txt'));
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        $form->column(6, function ($form) {
            $form->switch('publish', 'Публиковать');
            // $form->multipleImage('photos', __('Фотографии'))->removable()->downloadable();

            $form->html(function ($form) {
                $imagesBlock = '';
                foreach ( $form->model()->getMedia() as $image) {
                    $imagesBlock .= '<div class="file-preview-frame krajee-default">
                        <div class="kv-file-content">
                            <img src="' . $image->getUrl('catalog') . '"
                                class="file-preview-image kv-preview-data"
                                style="max-width:100%;max-height:100%;">
                        </div>
                        <div class="file-thumbnail-footer">
                            <div class="file-footer-caption" title="' . $image->file_name . '">
                                <div class="file-caption-info">' . $image->file_name . '</div>
                            </div>
                            <div class="file-actions">
                                <div class="file-footer-buttons">
                                    <button type="button" data-id="' . $image->id . '"
                                        class="kv-file-remove btn btn-sm btn-kv btn-default btn-outline-secondary">
                                        <i class="glyphicon glyphicon-trash"></i>
                                    </button>
                                    <button type="button" data-full="' . $image->getUrl('full') . '"
                                        class="kv-file-zoom btn btn-sm btn-kv btn-default btn-outline-secondary">
                                        <i class="glyphicon glyphicon-zoom-in"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>';
                }
                return '<div class="js-images-area">' . $imagesBlock . '</div>';
            });

            $form->html('<div class="input-group-btn input-group-append">
                <div tabindex="500" class="btn btn-primary btn-file">
                    <i class="glyphicon glyphicon-folder-open"></i>&nbsp;
                    <span class="hidden-xs">Выбор файла</span>
                    <input type="file" class="" name="photos[]" multiple id="imageLoader" accept="image/*">
                </div>
                <input type="hidden" name="add_images">
            </div>', 'Картинки');

            $form->text('slug', __('Slug'));
            $form->text('path', 'Путь')->disable();
            $form->text('title', 'Артикул')->required();
            $form->currency('buy_price', 'Цена покупки')->symbol('BYN');
            $form->currency('price', 'Цена')->symbol('BYN')->required();
            $form->currency('old_price', 'Старая цена')->symbol('BYN');
        });
        $form->column(6, function ($form) {
            $form->multipleSelect('sizes', 'Размеры')->options(Size::all()->pluck('name', 'id'))->required();
            $form->multipleSelect('colors', 'Цвет для фильтра')->options(Color::all()->pluck('name', 'id'));
            $form->multipleSelect('fabrics', 'Материал для фильтра')->options(Fabric::all()->pluck('name', 'id'));
            $form->multipleSelect('styles', 'Стиль')->options(Style::all()->pluck('name', 'id'));
            $form->multipleSelect('heels', 'Каблук/подошва')->options(Heel::all()->pluck('name', 'id'));
            $form->select('category_id', 'Категория')->options(Category::getFormatedTree())->required();
            $form->select('season_id', 'Сезон')->options(Season::all()->pluck('name','id'))->required();
            $form->select('brand_id', 'Бренд')->options(Brand::all()->pluck('name','id'))->required();
            $form->select('collection_id', 'Коллекция')->options(Collection::all()->pluck('name','id'))->required();
            $form->text('color_txt', 'Цвет');
            $form->text('fabric_top_txt', 'Материал верха');
            $form->text('fabric_inner_txt', 'Материал внутри');
            $form->text('fabric_insole_txt', 'Материал стельки');
            $form->text('fabric_outsole_txt', 'Материал подошвы');
            $form->text('bootleg_height_txt', 'Высота голенища');
            $form->text('heel_txt', 'Тип каблука/подошвы');

            $form->divider();
            $form->select('label_id', 'Метка')->options([
                0 => 'нет',
                1 => 'хит',
                2 => 'ликвидация',
                3 => 'не выгружать'
            ]);
            $form->text('rating', 'Рейтинг')->disable();
            $form->multipleSelect('tags', 'Теги')->options(Tag::all()->pluck('name', 'id'));
        });

        $form->column(12, function ($form) {
            $form->divider('Описание');
            $form->ckeditor('description', '');
        });

        $form->html('<div style="display: none;" id="crop-image">
            <div class="form-group">
                <button type="button" class="btn btn-primary" onclick="cropper.setAspectRatio(1)">
                    1 x 1
                </button>
                <button type="button" class="btn btn-primary" onclick="cropper.setAspectRatio(2/3)">
                    2 x 3
                </button>
                <button type="button" class="btn btn-default js-mask-toggler">
                    Скрыть/показать маску
                </button>
            </div>
            <div class="form-group">
                <canvas id="imageCanvas" style="max-width: 100%; max-height: 80vh;"></canvas>
            </div>
            <div class="form-group">
                <button class="btn btn-primary" id="save-cropped-image">Сохранить</button>
            </div>
        </div>
        <style>
        .fancybox-content {
            max-width: 80%;
            padding: 30px;
        }
        .fancybox-content .btn{
            padding: 6px 25px;
        }
        .cropper-face {
            background-color: unset;
            background-image: url("/images/admin/maska.png");
            background-size: cover;
            opacity: 0.8;
        }
        .cropper-face.hide-mask {
            background-image: unset;
        }
        </style>');

        $form->saving(function (Form $form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug(Brand::where('id', $form->brand_id)->value('name') . '-' . $form->title);
            }
        });

        $form->saved(function (Form $form) {

            // delete
            $removeImagesId = $form->input('remove_images') ?? [];
            Media::whereIn('id', $removeImagesId)->delete();
            // Storage::delete('file.jpg'); // !!!

            // add
            $addImages  = $form->input('add_images') ?? [];
            foreach ($addImages as $image) {
                $form->model()
                    ->addMedia(storage_path("app/$image"))
                    ->toMediaCollection();
            }

            $form->model()->url()->updateOrCreate(['slug' => $form->slug]);

            $this->sendToOldSite($form);
        });

        return $form;
    }
    /**
     * Сохранить товар в старую базу баддых
     *
     * @param \Encore\Admin\Form $form
     * @return void
     */
    protected function saveInOldDB(Form $form)
    {
        DB::connection('old_mysql')
            ->table('cyizj_jshopping_products')
            ->updateOrInsert(
            [
                'product_id' => $form->model()->id,
            ],
            [
                'parent_id' => 0,
                'product_ean' => '',
                'product_quantity' => 0,
                'unlimited' => 0,
                'product_availability' => '',
                'product_date_added' => date('Y-m-d H:i:s'),
                'date_modify' => date('Y-m-d H:i:s'),
                'product_publish' => $form->model()->publish,
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
                'product_manufacturer_id' => $this->getOldId('brand',  $form->brand_id),
                'product_is_add_price' => 0,
                'add_price_unit_id' => 3,
                'average_rating' => 0,
                'reviews_count' => 0,
                'delivery_times_id' => 0,
                'hits' => 0,
                'weight_volume_units' => 0,
                'basic_price_unit_id' => 0,
                'label_id' => 0,
                'vendor_id' => 0,
                'access' => 1,
                'name_en-GB' => '',
                'alias_en-GB' => '',
                'short_description_en-GB' => '',
                'description_en-GB' => '',
                'meta_title_en-GB' => '',
                'meta_description_en-GB' => '',
                'meta_keyword_en-GB' => '',
                'name_ru-RU' => $form->title,
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
                'extra_field_7' => $this->getOldId('season',  $form->season_id),
                'extra_field_8' => $form->fabric_inner_txt ?? '',
                'extra_field_9' => $form->fabric_insole_txt ?? '',
                'extra_field_10' => $form->fabric_outsole_txt ?? '',
                'extra_field_11' => $form->heel_txt ?? '',
                'extra_field_12' => '',
                'extra_field_13' => implode(',', array_filter(array_map(function ($value) {
                    return $this->getOldId('colors', $value);
                }, $form->colors))),
                'extra_field_14' => implode(',', array_filter(array_map(function ($value) {
                    return $this->getOldId('fabrics', $value);
                }, $form->fabrics))),
                'extra_field_15' => '',
                'extra_field_16' => '',
                'extra_field_17' => '',
                'extra_field_18' => 0,
            ]);

        $this->sendImagesAnOldSite($form);

        DB::connection('old_mysql')
            ->table('cyizj_jshopping_products_to_categories')
            ->updateOrInsert(
            [
                'product_id' => $form->model()->id,
            ],
            [
                'category_id' => $this->getOldId('category', $form->category_id),
                'product_ordering' => 1
            ]);

        $sizes = [];
        foreach ($form->sizes as $size) {
            $sizes[] = [
                'product_id' => $form->model()->id,
                'attr_id' => 2,
                'attr_value_id' => $this->getOldId('category', $size),
                'price_mod' => '+',
                'addprice ' => 0
            ];
        }
        DB::connection('old_mysql')
            ->table('cyizj_jshopping_products_attr2')
            ->where('product_id', $form->model()->id)
            ->delete();

        DB::connection('old_mysql')
            ->table('cyizj_jshopping_products_attr2')
            ->insert($sizes);
    }

    protected function getOldId($type, $newId)
    {
        $seeder = self::$productSeederObject ?? (self::$productSeederObject = new ProductSeeder);
        $oldIds = array_flip($seeder->attributesList[$type]['new_id'] ?? []);
        return $oldIds[$newId] ?? 0;
    }

    protected function sendImagesAnOldSite($form)
    {
        $data = [
            'token' => 'vTnD57Pdq45lkU',
            'id' => $form->model()->id,
            'update' => $form->isEditing(),
        ];
        foreach ($form->model()->getMedia() as $image) {
            $data['img'][] = $image->getUrl();
        }
        if (!empty($data['img'])) {
            $response = Http::asForm()->post('https://modny.by/saveimg_gRf5lP46jRm8s.php', $data);
            admin_info('Modny.by:', $response->body());
        }
    }
    /**
     * Отправить товары на старый сайт
     *
     * @param \Encore\Admin\Form $form
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
                'product_publish' => $form->model()->publish,
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
                'product_manufacturer_id' => $this->getOldId('brand',  $form->brand_id),
                'product_is_add_price' => 0,
                'add_price_unit_id' => 3,
                'average_rating' => 0,
                'reviews_count' => 0,
                'delivery_times_id' => 0,
                'hits' => 0,
                'weight_volume_units' => 0,
                'basic_price_unit_id' => 0,
                'label_id' => 0,
                'vendor_id' => 0,
                'access' => 1,
                'name_en-GB' => '',
                'alias_en-GB' => '',
                'short_description_en-GB' => '',
                'description_en-GB' => '',
                'meta_title_en-GB' => '',
                'meta_description_en-GB' => '',
                'meta_keyword_en-GB' => '',
                'name_ru-RU' => $form->title,
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
                'extra_field_7' => $this->getOldId('season',  $form->season_id),
                'extra_field_8' => $form->fabric_inner_txt ?? '',
                'extra_field_9' => $form->fabric_insole_txt ?? '',
                'extra_field_10' => $form->fabric_outsole_txt ?? '',
                'extra_field_11' => $form->heel_txt ?? '',
                'extra_field_12' => '',
                'extra_field_13' => implode(',', array_filter(array_map(function ($value) {
                    return $this->getOldId('colors', $value);
                }, $form->colors))),
                'extra_field_14' => implode(',', array_filter(array_map(function ($value) {
                    return $this->getOldId('fabrics', $value);
                }, $form->fabrics))),
                'extra_field_15' => '',
                'extra_field_16' => '',
                'extra_field_17' => '',
                'extra_field_18' => 0,
            ],
            'category' => [
                'product_id' => $form->model()->id,
                'category_id' => $this->getOldId('category', $form->category_id),
                'product_ordering' => 1
            ],
            'sizes' => array_map(function ($size) use ($form) {
                return [
                    'product_id' => $form->model()->id,
                    'attr_id' => 2,
                    'attr_value_id' => $this->getOldId('category', $size),
                    'price_mod' => '+',
                    'addprice ' => 0
                ];
            }, $form->sizes),
            'images' => $form->model()->getMedia()->map(function ($image) {
                return $image->getUrl();
            })->toArray(),
        ];

        $data = [
            'token' => 'vTnD57Pdq45lkU',
            'data' => $data
        ];

        $response = Http::asForm()->post('https://modny.by/saveimg_gRf5lP46jRm8s.php', $data);
        admin_info('Modny.by:', $response->body());
    }
}
