<?php

namespace App\Enums\Ads;

enum BannerImageSize: string
{
    case THUMB = 'thumb';
    case XSMALL = 'xsmall';
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';

    public function isThumb(): bool
    {
        return $this === self::THUMB;
    }

    /**
     * @return array<string, string> format => Spatie conversion name
     */
    public function conversionNames(): array
    {
        if ($this->isThumb()) {
            return ['jpg' => $this->value];
        }

        return [
            'jpg' => $this->value,
            'webp' => "{$this->value}-webp",
        ];
    }
}
