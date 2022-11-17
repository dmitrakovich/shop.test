<?php

namespace App\Libraries\Seo\Contracts;

interface SeoContract
{
    /**
     * Set title.
     * @param string|null $title
     * @return self
     */
    public function setTitle(?string $title): self;

    /**
     * Set description
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self;

    /**
     * Set image
     * @param string|null $image
     * @return self
     */
    public function setImage(null|string|array $image): self;

    /**
     * Set url
     * @param string|null $url
     * @return self
     */
    public function setUrl(?string $url): self;
}
