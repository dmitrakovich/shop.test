<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use SoftDeletes;
    use NodeTrait;
    use AttributeFilterTrait;

    public $timestamps = false;
    protected static $relationName = 'category';
    protected static $relationTable = 'categories';
    //
    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
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
        return $this->hasMany(Category::class, 'parent_id')->with('childrenCategories');
    }














    // Получение ссылки
    public function getUrl()
    {
        return '/' . $this->path;
    }

    public function generatePath()
    {
        $slug = $this->slug;
        $this->path = $this->isRoot() ? $slug : $this->parent->path . '/' . $slug;
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
}
