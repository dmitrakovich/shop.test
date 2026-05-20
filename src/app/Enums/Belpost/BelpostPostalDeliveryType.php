<?php

namespace App\Enums\Belpost;

use Filament\Support\Contracts\HasLabel;

enum BelpostPostalDeliveryType: string implements HasLabel
{
    case OrderedPostcard = 'ordered_postcard';
    case OrderedLetter = 'ordered_letter';
    case OrderedParcelPost = 'ordered_parcel_post';
    case OrderedSmallPackage = 'ordered_small_package';
    case SmallPackageDeclareValue = 'small_package_declare_value';
    case Package = 'package';
    case PackageDeclareValue = 'package_declare_value';
    case Ems = 'ems';
    case EcommerceEconomical = 'ecommerce_economical';
    case EcommerceStandard = 'ecommerce_standard';
    case EcommerceElite = 'ecommerce_elite';
    case EcommerceExpress = 'ecommerce_express';
    case EcommerceLight = 'ecommerce_light';
    case EcommerceOptima = 'ecommerce_optima';

    /**
     * Belarus Post rules: e‑commerce parcel tariffs do not accept partial enclosure receipt;
     * turning it on in the cabinet leads to mandatory attachment lines we do not populate.
     */
    public function isEcommercePostal(): bool
    {
        return str_starts_with($this->value, 'ecommerce_');
    }

    /**
     * Whether `is_partial_receipt` may be synced to the API as true with this tariff.
     */
    public function supportsPartialReceiptOfEnclosures(): bool
    {
        return !$this->isEcommercePostal();
    }

    /** Party is только документы (письма, карточки, бандероль) — Белпочте нужен признак «документы». */
    public function isDocumentOnlyShipmentTariff(): bool
    {
        return match ($this) {
            self::OrderedLetter, self::OrderedPostcard, self::OrderedParcelPost => true,
            default => false,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::OrderedPostcard => 'Заказная почтовая карточка',
            self::OrderedLetter => 'Заказное письмо',
            self::OrderedParcelPost => 'Заказная бандероль',
            self::OrderedSmallPackage => 'Заказной мелкий пакет',
            self::SmallPackageDeclareValue => 'Мелкий пакет с ОЦ',
            self::Package => 'Простая посылка',
            self::PackageDeclareValue => 'Посылка с ОЦ',
            self::Ems => 'EMS',
            self::EcommerceEconomical => 'E-commerce Эконом',
            self::EcommerceStandard => 'E-commerce Стандарт',
            self::EcommerceElite => 'E-commerce Элит',
            self::EcommerceExpress => 'E-commerce Экспресс',
            self::EcommerceLight => 'E-commerce Лайт',
            self::EcommerceOptima => 'E-commerce Оптима',
        };
    }
}
