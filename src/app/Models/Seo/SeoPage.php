<?php

namespace App\Models\Seo;

use App\Enums\Seo\SeoPageType;
use Illuminate\Database\Eloquent\Model;
use League\Uri\Uri;

/**
 * @property int $id
 * @property SeoPageType $page_type
 * @property string $url
 * @property string|null $title
 * @property string|null $description
 * @property string|null $h1
 * @property string|null $seo_text_title
 * @property string|null $seo_text
 * @property string|null $keywords
 * @property string|null $tag_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SeoPage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'page_type',
        'url',
        'title',
        'description',
        'h1',
        'seo_text_title',
        'seo_text',
        'keywords',
        'tag_name',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_type' => SeoPageType::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SeoPage $page): void {
            if ($page->isDirty(['url', 'page_type']) && filled($page->url)) {
                $page->url = self::normalizeUrl($page->url, $page->page_type);
            }
        });
    }

    public static function normalizeUrl(string $value, SeoPageType $pageType): string
    {
        $uri = Uri::parse($value);

        if ($uri === null) {
            $result = ltrim($value, '/');
        } else {
            $result = ltrim($uri->getPath(), '/');
            $query = $uri->getQuery();

            if (filled($query)) {
                $result .= '?' . $query;
            }
        }

        $prefix = $pageType->value;

        if (str_starts_with($result, $prefix . '/')) {
            $result = substr($result, strlen($prefix) + 1);
        } elseif ($result === $prefix) {
            $result = '';
        }

        return $prefix . ($result !== '' ? '/' . $result : '');
    }
}
