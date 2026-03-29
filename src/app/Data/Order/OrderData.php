<?php

namespace App\Data\Order;

use App\Data\Casts\ModelCast;
use App\Enums\Order\OrderMethod;
use App\Enums\Order\OrderTypeEnum as OrderType;
use App\Facades\Currency as CurrencyFacade;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Stock;
use App\ValueObjects\Phone;
use Deliveries\DeliveryMethod;
use Deliveries\ShopPvz;
use Jenssegers\Agent\Facades\Agent;
use Payments\PaymentMethod;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\ProhibitedUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredIf;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class OrderData extends Data
{
    #[Max(50)]
    public string $firstName;

    #[Max(50)]
    public ?string $patronymicName = null;

    #[Max(50)]
    public ?string $lastName = null;

    #[WithCastable(Phone::class)]
    public Phone $phone;

    #[Max(50)]
    public ?string $email = null;

    public ?string $comment = null;

    #[MapInputName('payment_id')]
    #[WithCast(ModelCast::class, modelClass: PaymentMethod::class)]
    public ?PaymentMethod $paymentMethod = null;

    #[MapInputName('delivery_id')]
    #[WithCast(ModelCast::class, modelClass: DeliveryMethod::class)]
    public ?DeliveryMethod $deliveryMethod = null;

    public OrderMethod $orderMethod = OrderMethod::DEFAULT;

    #[Computed]
    public OrderType $orderType;

    #[MapInputName('country_id')]
    #[WithCast(ModelCast::class, modelClass: Country::class)]
    public ?Country $country = null;

    #[Max(50)]
    public ?string $region = null;

    #[Max(50)]
    public ?string $city = null;

    #[Max(10)]
    public ?int $zip = null;

    #[Max(191)]
    public ?string $userAddr = null;

    #[MapInputName('stock_id')]
    #[WithCast(ModelCast::class, modelClass: Stock::class)]
    #[RequiredIf('delivery_id', [ShopPvz::ID]), ProhibitedUnless('delivery_id', [ShopPvz::ID])]
    public ?Stock $stock = null;

    #[Computed]
    public Currency $currency;

    public ?string $utmMedium = null;

    public ?string $utmSource = null;

    public ?string $utmCampaign = null;

    public ?string $utmContent = null;

    public ?string $utmTerm = null;

    public function __construct()
    {
        $this->orderType = Agent::isDesktop() ? OrderType::DESKTOP : OrderType::MOBILE;
        $this->currency = CurrencyFacade::getCurrentCurrency();
    }

    public function setOrderMethod(OrderMethod $orderMethod): void
    {
        $this->orderMethod = $orderMethod;
    }

    public function prepareToSave(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'patronymic_name' => $this->patronymicName,
            // 'promocode_id' => $this->promocode_id,
            'email' => $this->email,
            'phone' => $this->phone->forSave(),
            'comment' => $this->comment,
            'currency' => $this->currency->code,
            'rate' => $this->currency->rate,
            'country_id' => $this->country?->id,
            'region' => $this->region,
            'city' => $this->city,
            'zip' => $this->zip,
            'user_addr' => $this->userAddr,
            'payment_id' => $this->paymentMethod?->id,
            // 'payment_cost' => $this->payment_cost,
            'delivery_id' => $this->deliveryMethod?->id,
            'stock_id' => $this->stock?->id,
            // 'delivery_cost' => $this->delivery_cost,
            // 'delivery_price' => $this->delivery_price,
            'order_method' => $this->orderMethod,
            'order_type' => $this->orderType,
            'utm_medium' => $this->utmMedium,
            'utm_source' => $this->utmSource,
            'utm_campaign' => $this->utmCampaign,
            'utm_content' => $this->utmContent,
            'utm_term' => $this->utmTerm,
        ];
    }
}
