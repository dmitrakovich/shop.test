<?php

namespace App\Models;

use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use App\Traits\AttributeFilterTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Product category class
 *
 * @property int $id
 * @property string $slug
 * @property string $path
 * @property string $title
 * @property-read string $name
 * @property string $description
 */
class Category extends Model
{
    use SoftDeletes, NodeTrait, AttributeFilterTrait;

    public $timestamps = false;

    /**
     * @var int
     */
    const ROOT_CATEGORY_ID = 1;

    /**
     * @var int
     */
    const ACCESSORIES_PARENT_ID = 25;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function getRelationColumn()
    {
        return 'category_id';
    }

    public function setPathAttribute($path)
    {
        $this->generatePath();
    }

    public function parentCategory()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')
            // ->where('active', true)
            ->with('childrenCategories');
    }

    public static function beforeApplyFilter(&$builder, &$values)
    {
        $currentCategoryId = end($values);
        if ($currentCategoryId === self::ROOT_CATEGORY_ID) {
            $values = false;
        } else {
            $values = self::getChildrenCategoriesIdsList($currentCategoryId);
        }
    }
    /**
     * Получить список идентификаторов дочерних категорий
     *
     * @param integer $categoryId
     * @return array
     */
    public static function getChildrenCategoriesIdsList(int $categoryId): array
    {
        return Cache::rememberForever("categoryChilds.$categoryId", function () use ($categoryId) {
            return self::traverseTree(
                self::with('childrenCategories')->find($categoryId)->toArray()
            );
        });
    }
    /**
     * Сделать одноуровневый массив из дерева
     *
     * @param array $subtree
     * @return array
     */
    protected static function traverseTree(array $subtree)
    {
        $descendants[] = $subtree['id'];
        foreach ($subtree['children_categories'] as $child) {
            $descendants = array_merge($descendants, self::traverseTree($child));
        }
        return $descendants;
    }
    // Получение ссылки
    public function getUrl()
    {
        return '/' . $this->path;
    }

    public function generatePath()
    {
        $slug = $this->slug;
        $this->attributes['path'] = $this->isRoot() ? $slug : $this->parent->path . '/' . $slug;
        return $this;
    }

    public function updateDescendantsPaths()
    {
        // Получаем всех потомков в древовидном порядке
        $descendants = $this->descendants()->defaultOrder()->get();

        // Данный метод заполняет отношения parent и children
        $descendants->push($this)->linkNodes()->pop();

        foreach ($descendants as $model) {
            $model->generatePath()->save();
        }
    }
    /**
     * Получить форматированное дерево категорий
     *
     * @return array
     */
    public static function getFormatedTree()
    {
        $nodes = self::whereNotNull('parent_id')->get()->toTree();

        $traverse = function ($categories, $prefix = '', &$result = []) use (&$traverse) {
            foreach ($categories as $category) {
                $result[$category->id] = $prefix . $category->title;

                $traverse($category->children, $prefix.'---- ', $result);
            }
            return $result;
        };

        return $traverse($nodes);
    }

    /**
     * Make dafault category
     *
     * @return self
     */
    public static function getDefault()
    {
        return self::make([
            'id' => 1,
            'slug' => 'catalog',
            'path' => 'catalog',
            'title' => 'Каталог',
        ]);
    }

    /**
     * Get category name (accessor)
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->isRoot() ? 'Женская обувь' : $this->title;
    }

    /**
     * Check category is root
     *
     * @return boolean
     */
    protected function isRoot(): bool
    {
        return $this->id === self::ROOT_CATEGORY_ID;
    }

    /**
     * Prepare name for catalog page title
     *
     * @return string
     */
    public function getNameForCatalogTitle(): string
    {
        $name = $this->isRoot() ? 'женскую обувь' : $this->name;
        return Str::lower($name);
    }

    /**
     * Get category name with parents categories names
     *
     * @return string
     */
    public function getNameWithParents(): string
    {
        $category = $this;
        $name = $category->name;
        while ($category->parentCategory) {
            $category = $category->parentCategory;
            $name = $category->name . '/' . $name;
        }
        return $name;
    }
}
