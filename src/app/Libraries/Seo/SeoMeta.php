<?php

namespace App\Libraries\Seo;

use App\Libraries\Seo\Contracts\SeoContract;

class SeoMeta implements SeoContract
{
    protected $config; // @var Config

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Generate meta tags
     *
     * @return string
     */
    public function generate(): string
    {
        $html = [];
        foreach ($this->config as $key => $value) {
            if ($value !== false) {
                switch ($key) {
                    case 'title':
                        $html[] = '<title>' . (string)$value . '</title>';
                        break;
                    case 'description':
                        $html[] = '<meta name="description" content="' . (string)$value . '">';
                        break;
                    case 'url':
                        $value = is_null($value) ? htmlspecialchars(url()->current()) : $value;
                        $html[] = '<link rel="canonical" href="' . url((string)$value) . '"/>';
                        break;
                    case 'robots':
                        if ($value) {
                            $html[] = "<meta name=\"robots\" content=\"{$value}\">";
                        } else {
                            $html[] = '<meta name="robots" content="all">';
                        }
                        break;
                    case 'keywords':
                        if ($value instanceof \Illuminate\Support\Collection) {
                            $value = $value->toArray();
                        }
                        $value = is_array($value) ? implode(', ', $value) : $value;
                        if ($value) {
                            $html[] = '<meta name="keywords" content="' . (string)$value . '">';
                        }
                        break;
                }
            }
        }

        return implode(PHP_EOL, $html);
    }

    /**
     * @param  string  $key
     * @param  string|null|bool  $value
     * @return self
     */
    private function addToConfig(string $key, string|null|bool $value): self
    {
        if (!is_null($value)) {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * Set keywords
     *
     * @param  string|array|null  $keywords
     * @return self
     */
    public function setKeywords(string|array|null $keywords): self
    {
        if ($keywords) {
            if (!is_array($keywords)) {
                $keywords = array_map('trim', explode(',', $keywords));
            }
            $keywords = array_map('strip_tags', $keywords);
        }

        return $this->addToConfig('keywords', $keywords);
    }

    /**
     * Set title.
     *
     * @param  string|null  $title
     * @return self
     */
    public function setTitle(?string $title): self
    {
        if ($title && trim($title)) {
            return $this->addToConfig('title', $title);
        }

        return $this;
    }

    /**
     * Set description
     *
     * @param  string|null  $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        if ($description && trim($description)) {
            return $this->addToConfig('description', htmlspecialchars($description, ENT_QUOTES, 'UTF-8', false));
        }

        return $this;
    }

    public function setImage(null|string|array $image): self
    {
        return $this;
    }

    /**
     * Set url
     *
     * @param  string|null  $url
     * @return self
     */
    public function setUrl(?string $url): self
    {
        return $this->addToConfig('url', $url);
    }

    /**
     * Set robots
     *
     * @param  string|null  $robots
     * @return self
     */
    public function setRobots(?string $robots): self
    {
        if (trim($robots)) {
            return $this->addToConfig('robots', $robots);
        }

        return $this;
    }
}
