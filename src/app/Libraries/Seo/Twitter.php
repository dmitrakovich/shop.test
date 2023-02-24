<?php

namespace App\Libraries\Seo;

use App\Libraries\Seo\Contracts\SeoContract;

class Twitter implements SeoContract
{
    protected $config = []; // @var array

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Generate meta tags
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
                $key = 'twitter:' . strip_tags($key);
                $html[] = '<meta name="' . $key . '" content="' . strip_tags((string)$value) . '"/>';
            }
        }

        return implode(PHP_EOL, $html);
    }

    private function addToConfig(string $key, string|null|bool $value): self
    {
        if (!is_null($value)) {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * Set title.
     */
    public function setTitle(?string $title): self
    {
        return $this->addToConfig('title', $title);
    }

    /**
     * Set description
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
     */
    public function setUrl(?string $url): self
    {
        return $this->addToConfig('url', $url);
    }
}
