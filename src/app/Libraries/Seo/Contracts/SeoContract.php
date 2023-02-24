<?php

namespace App\Libraries\Seo\Contracts;

interface SeoContract
{
    /**
     * Set title.
     */
    public function setTitle(?string $title): self;

    /**
     * Set description
     */
    public function setDescription(?string $description): self;

    /**
     * Set image
     *
     * @param  string|null  $image
     */
    public function setImage(null|string|array $image): self;

    /**
     * Set url
     */
    public function setUrl(?string $url): self;
}
