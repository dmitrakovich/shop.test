<?php

namespace App\Enums\Order;

enum UtmEnum: string
{
    case GOOGLE_ADS = 'googleads-max_by';
    case INSTAGRAM_MANAGER = 'instagram-manager';
    case INSTAGRAM_ACCOUNT = 'instagram-accaunt_link';
    case VIBER_PROMO = 'viber-promo';
    case YANDEX_SHOPPING = 'yandex-shopping';
    case YANDEX_RETARGET = 'yandex-by_dyn_retarget';
    case FB_CATALOG_RETARGET = 'fb.com-catalog_retarget_all14';
    case VIBER_CHANNEL = 'viber-channel';
    case PHONE_MANAGER = 'phone-manager';
    case FB_TARGET_CART = 'fb.com-conv_target-cart_ki-full';
    case INSTAGRAM_STORIES = 'instagram-stories';
    case YANDEX_CONTEXT_ASSORTIMENT = 'yandex-context_assortiment';
    case TELEGRAM_CHANNEL = 'telegram-channel';
    case GOOGLE_ADS_REMARCETING = 'googleads-dyn_remarceting_by';
    case VIBER_AUTO = 'viber-auto';
    case YANDEX_ASSORTIMENT = 'yandex-search_assortiment';
    case INSTAGRAM_MANAGERLINK = 'instagram-managerlink';
    case YANDEX_CONTEXT_CHANNEL = 'yandex-context_channel';
    case YANDEX_UNI_PERFORMANCE = 'yandex-uni_performance';

    /**
     * Returns the channel name based on the value of $this.
     *
     * @return ?string The channel name as a string or null if $this does not match any of the cases.
     */
    public function channelName(): ?string
    {
        return match ($this) {
            self::GOOGLE_ADS => 'Google Ads',
            self::INSTAGRAM_MANAGER => 'Instagram',
            self::INSTAGRAM_ACCOUNT => 'Instagram',
            self::VIBER_PROMO => 'Viber',
            self::YANDEX_SHOPPING => 'Яндекс',
            self::YANDEX_RETARGET => 'Яндекс',
            self::FB_CATALOG_RETARGET => 'FB Ads',
            self::VIBER_CHANNEL => 'Viber',
            self::PHONE_MANAGER => 'Телефон',
            self::FB_TARGET_CART => 'FB Ads',
            self::INSTAGRAM_STORIES => 'Instagram',
            self::YANDEX_CONTEXT_ASSORTIMENT => 'Яндекс',
            self::TELEGRAM_CHANNEL => 'Telegram',
            self::GOOGLE_ADS_REMARCETING => 'Google Ads',
            self::VIBER_AUTO => 'Viber',
            self::YANDEX_ASSORTIMENT => 'Яндекс',
            self::INSTAGRAM_MANAGERLINK => 'Instagram',
            self::YANDEX_CONTEXT_CHANNEL => 'Яндекс',
            self::YANDEX_UNI_PERFORMANCE => 'Яндекс',
        };
    }

    /**
     * Returns the name of the company based on the current instance.
     *
     * @return string|null The name of the company. Returns null if the company is not recognized.
     */
    public function companyName(): ?string
    {
        return match ($this) {
            self::GOOGLE_ADS => 'Товарная',
            self::INSTAGRAM_MANAGER => 'менеджер',
            self::INSTAGRAM_ACCOUNT => 'Профиль',
            self::VIBER_PROMO => 'Рассылка',
            self::YANDEX_SHOPPING => 'Товарная',
            self::YANDEX_RETARGET => 'Ретаргет',
            self::FB_CATALOG_RETARGET => 'Ретаргет',
            self::VIBER_CHANNEL => 'Канал',
            self::PHONE_MANAGER => 'менеджер',
            self::FB_TARGET_CART => 'Прямая реклама',
            self::INSTAGRAM_STORIES => 'Stories',
            self::YANDEX_CONTEXT_ASSORTIMENT => 'РСЯ',
            self::TELEGRAM_CHANNEL => 'Канал',
            self::GOOGLE_ADS_REMARCETING => 'Ретаргет',
            self::VIBER_AUTO => 'Авторассылка',
            self::YANDEX_ASSORTIMENT => 'Поиск',
            self::INSTAGRAM_MANAGERLINK => 'Ссылка менеджера',
            self::YANDEX_CONTEXT_CHANNEL => 'Реклама каналов',
            self::YANDEX_UNI_PERFORMANCE => 'Универсальная кампания',
        };
    }
}
