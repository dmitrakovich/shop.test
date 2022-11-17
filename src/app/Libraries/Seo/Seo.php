<?php

namespace App\Libraries\Seo;

use App\Libraries\Seo\Contracts\SeoContract;

class Seo implements SeoContract
{
    protected ?string $h1    = null;
    protected ?string $title = null;

    public function meta()
    {
        return app('seo.meta');
    }

    public function opengraph()
    {
        return app('seo.opengraph');
    }

    public function twitter()
    {
        return app('seo.twitter');
    }

    public function generateHead(): string
    {
        $html  = $this->meta()->generate();
        $html .= PHP_EOL;
        $html .= $this->opengraph()->generate();
        $html .= PHP_EOL;
        $html .= $this->twitter()->generate();
        return $html;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setH1(string $h1 = ''): self
    {
        $this->h1 = $h1;
        return $this;
    }

    /**
     * Set keywords
     * @param string|array|null $keywords
     * @return self
     */
    public function setKeywords(string|array|null $keywords): self
    {
        $this->meta()->setKeywords($keywords);
        return $this;
    }

    /**
     * Set title.
     * @param string|null $title
     * @return self
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        $this->meta()->setTitle($title);
        $this->opengraph()->setTitle($title);
        $this->twitter()->setTitle($title);
        return $this;
    }

    /**
     * Set description
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->meta()->setDescription($description);
        $this->opengraph()->setDescription($description);
        $this->twitter()->setDescription($description);
        return $this;
    }

    /**
     * Set image
     * @param null|string|array $image
     * @return self
     */
    public function setImage(null|string|array $image): self
    {
        $this->meta()->setImage($image);
        $this->opengraph()->setImage($image);
        $this->twitter()->setImage($image);
        return $this;
    }

    /**
     * Set url
     * @param string|null $url
     * @return self
     */
    public function setUrl(?string $url): self
    {
        $this->meta()->setUrl($url);
        $this->opengraph()->setUrl($url);
        $this->twitter()->setUrl($url);
        return $this;
    }

    /**
     * Set robots
     * @param string|null $robots
     * @return self
     */
    public function setRobots($robots)
    {
        $this->meta()->setRobots($robots);
        return $this;
    }
}
