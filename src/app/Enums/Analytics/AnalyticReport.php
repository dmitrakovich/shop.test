<?php

namespace App\Enums\Analytics;

use Filament\Support\Contracts\HasLabel;
use Staudenmeir\LaravelCte\Query\Builder;

enum AnalyticReport: string implements HasLabel
{
    case ManagerCustomers = 'manager-customers';
    case ManagerOrderItems = 'manager-order-items';
    case OrderSources = 'order-sources';
    case OrderSourceDetails = 'order-source-details';
    case OrderTypes = 'order-types';
    case PaymentMethods = 'payment-methods';
    case DeliveryMethods = 'delivery-methods';
    case Countries = 'countries';

    public function getLabel(): string
    {
        return match ($this) {
            self::ManagerCustomers => 'Менеджер-покупатель',
            self::ManagerOrderItems => 'Менеджер-товар',
            self::OrderSources => 'Источники',
            self::OrderSourceDetails => 'Источники PRO',
            self::OrderTypes => 'Тип заказа',
            self::PaymentMethods => 'Способы оплаты',
            self::DeliveryMethods => 'Способы доставки',
            self::Countries => 'Страна',
        };
    }

    public function dimensionLabel(): string
    {
        return match ($this) {
            self::ManagerCustomers, self::ManagerOrderItems => 'Менеджер',
            self::OrderSources, self::OrderSourceDetails => 'Источник заказа',
            self::OrderTypes => 'Тип заказа',
            self::PaymentMethods => 'Способ оплаты',
            self::DeliveryMethods => 'Способ доставки',
            self::Countries => 'Страна',
        };
    }

    public function navigationSort(): int
    {
        return match ($this) {
            self::ManagerCustomers => 1,
            self::ManagerOrderItems => 2,
            self::OrderSources => 3,
            self::OrderSourceDetails => 4,
            self::OrderTypes => 5,
            self::PaymentMethods => 6,
            self::DeliveryMethods => 7,
            self::Countries => 8,
        };
    }

    /**
     * Legacy menu used ?default-filter for all reports except source-detail.
     */
    public function hasDefaultDateFilter(): bool
    {
        return $this !== self::OrderSourceDetails;
    }

    public function aggregation(): AnalyticAggregation
    {
        return match ($this) {
            self::Countries,
            self::ManagerCustomers,
            self::OrderTypes,
            self::OrderSources,
            self::OrderSourceDetails => AnalyticAggregation::Customer,
            self::PaymentMethods,
            self::DeliveryMethods,
            self::ManagerOrderItems => AnalyticAggregation::OrderItem,
        };
    }

    public function showsUtmDetails(): bool
    {
        return $this === self::OrderSourceDetails;
    }

    public function instanceNameExpression(): string
    {
        return match ($this) {
            self::Countries => 'countries.name',
            self::PaymentMethods => 'payment_methods.name',
            self::DeliveryMethods => 'delivery_methods.name',
            self::ManagerCustomers,
            self::ManagerOrderItems => 'CONCAT(admin_users.user_last_name, \' \', SUBSTRING(admin_users.name, 1, 1), \'.\')',
            self::OrderTypes => 'orders.order_type',
            self::OrderSources,
            self::OrderSourceDetails => 'CONCAT(orders.utm_source, \'-\', orders.utm_campaign)',
        };
    }

    /**
     * @param  Builder  $query
     */
    public function configureQuery(Builder $query): void
    {
        match ($this) {
            self::Countries => $query
                ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                ->leftJoin('user_addresses', 'user_addresses.user_id', '=', 'users.id')
                ->leftJoin('countries', 'countries.id', '=', 'user_addresses.country_id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('countries.id'),
            self::PaymentMethods => $query
                ->leftJoin('payment_methods', 'orders.payment_id', '=', 'payment_methods.id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('payment_methods.id'),
            self::DeliveryMethods => $query
                ->leftJoin('delivery_methods', 'orders.delivery_id', '=', 'delivery_methods.id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('delivery_methods.id'),
            self::ManagerCustomers => $query
                ->leftJoin('admin_users', 'orders.admin_id', '=', 'admin_users.id')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('admin_users.id'),
            self::ManagerOrderItems => $query
                ->leftJoin('admin_users', 'orders.admin_id', '=', 'admin_users.id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('admin_users.id'),
            self::OrderTypes => $query
                ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('orders.order_type'),
            self::OrderSources,
            self::OrderSourceDetails => $query
                ->leftJoin('users', 'users.id', '=', 'orders.user_id')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->groupBy('instance_name'),
        };
    }
}
