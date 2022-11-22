<?php

namespace App\Libraries\Seo;

use App\Libraries\Seo\Contracts\SeoContract;

class OpenGraph implements SeoContract
{
    protected $config = []; // @var array - Config.

    protected $productConfig = []; // @var array - Array of Product Properties.

    public function __construct(array $config = [])
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
        if (!isset($this->config['url'])) {
            $this->config['url'] = htmlspecialchars(url()->current());
        }
        foreach ($this->config as $key => $value) {
            if ($value !== false) {
                if ($key == 'image' || $key == 'url') {
                    $value = secure_url($value);
                }
                $key = 'og:' . strip_tags($key);
                $html[] = '<meta property="' . $key . '" content="' . strip_tags((string)$value) . '"/>';
            }
        }
        foreach ($this->productConfig as $key => $value) {
            if ($value !== false) {
                $key = 'product:' . strip_tags($key);
                $html[] = '<meta property="' . $key . '" content="' . strip_tags((string)$value) . '"/>';
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
     * Set product properties
     *
     * @param  array  $attributes opengraph product attributes
     * @return self
     */
    public function setProduct(array $attributes = []): self
    {
        $validKeys = [
            'price:amount',
            'price:currency',
        ];
        foreach ($attributes as $key => $value) {
            if (in_array($key, $validKeys)) {
                $this->productConfig[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Set title.
     *
     * @param  string|null  $title
     * @return self
     */
    public function setTitle(?string $title): self
    {
        return $this->addToConfig('title', $title);
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

    /**
     * Set image
     *
     * @param  string|null  $image
     * @return self
     */
    public function setImage(null|string|array $image): self
    {
        if (is_string($image)) {
            return $this->addToConfig('image', $image);
        }
        if (is_array($image)) {
            return $this->addToConfig('image', $image[0]);
        }

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
}
