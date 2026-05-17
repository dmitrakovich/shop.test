<?php

namespace App\Enums\Ads;

use Filament\Support\Contracts\HasLabel;

enum BannerPosition: string implements HasLabel
{
    case INDEX_MAIN = 'index_main';
    case INDEX_DOUBLE = 'index_double';
    case INDEX_CATEGORY = 'index_category';
    case CATALOG_MAIN = 'catalog_main';
    case FEEDBACK_MAIN = 'feedback_main';

    public function getLabel(): string
    {
        return match ($this) {
            self::INDEX_MAIN => 'На главной: основной',
            self::INDEX_DOUBLE => 'На главной: двойной',
            self::INDEX_CATEGORY => 'На главной: категории',
            self::CATALOG_MAIN => 'В каталоге',
            self::FEEDBACK_MAIN => 'В отзывах',
        };
    }

    /**
     * These slots only use desktop creatives in Filament; {@see BannerForm} hides mobile uploads.
     */
    public function isDesktopOnly(): bool
    {
        return match ($this) {
            self::INDEX_DOUBLE, self::INDEX_CATEGORY => true,
            default => false,
        };
    }

    /**
     * Target aspect ratio for Filament image crop (width:height).
     */
    public function imageAspectRatio(bool $isDesktop): string
    {
        return match ($this) {
            self::INDEX_MAIN => $isDesktop ? '16:9' : '9:16',
            self::INDEX_DOUBLE => '6:5',
            self::INDEX_CATEGORY => '4:5',
            self::CATALOG_MAIN, self::FEEDBACK_MAIN => $isDesktop ? '720:133' : '3:1',
        };
    }

    /**
     * Max output width per conversion (height follows cropped aspect ratio).
     *
     * @return array<value-of<BannerImageSize>, positive-int>
     */
    public function imageConversionWidths(bool $isDesktop): array
    {
        return match ($this) {
            self::INDEX_MAIN => $isDesktop
                ? [
                    BannerImageSize::THUMB->value => 72,
                    BannerImageSize::SMALL->value => 1280,
                    BannerImageSize::MEDIUM->value => 1920,
                    BannerImageSize::LARGE->value => 2560,
                ]
                : [
                    BannerImageSize::THUMB->value => 40,
                    BannerImageSize::SMALL->value => 540,
                    BannerImageSize::MEDIUM->value => 720,
                    BannerImageSize::LARGE->value => 1080,
                ],
            self::INDEX_DOUBLE => [
                BannerImageSize::THUMB->value => 48,
                BannerImageSize::XSMALL->value => 540,
                BannerImageSize::SMALL->value => 720,
                BannerImageSize::MEDIUM->value => 1080,
                BannerImageSize::LARGE->value => 1440,
            ],
            self::INDEX_CATEGORY => [
                BannerImageSize::THUMB->value => 40,
                BannerImageSize::XSMALL->value => 240,
                BannerImageSize::SMALL->value => 320,
                BannerImageSize::MEDIUM->value => 480,
                BannerImageSize::LARGE->value => 640,
            ],
            self::CATALOG_MAIN, self::FEEDBACK_MAIN => $isDesktop
                ? [
                    BannerImageSize::THUMB->value => 216,
                    BannerImageSize::SMALL->value => 720,
                    BannerImageSize::MEDIUM->value => 1080,
                    BannerImageSize::LARGE->value => 1440,
                ]
                : [
                    BannerImageSize::THUMB->value => 120,
                    BannerImageSize::SMALL->value => 540,
                    BannerImageSize::MEDIUM->value => 720,
                    BannerImageSize::LARGE->value => 1080,
                ],
        };
    }

    public static function defaultForCreate(): self
    {
        $activeTab = request()->query('activeTab');

        if (is_string($activeTab) && $activeTab !== '' && $activeTab !== 'all') {
            return self::tryFrom($activeTab) ?? self::INDEX_MAIN;
        }

        return self::INDEX_MAIN;
    }

    /**
     * Resolve Filament form state (enum instance or string) to a position.
     */
    public static function fromFormState(mixed $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            return self::tryFrom($value) ?? self::defaultForCreate();
        }

        return self::defaultForCreate();
    }
}
