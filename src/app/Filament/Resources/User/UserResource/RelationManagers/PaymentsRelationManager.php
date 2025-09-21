<?php

namespace App\Filament\Resources\User\UserResource\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use App\Models\Payments\OnlinePayment;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Платежи';

    public function table(Table $table): Table
    {
        $table->modifyQueryUsing(
            fn (Builder $query) => $query->orderBy('id', 'desc')
        );

        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Дата/время создания')
                    ->dateTime('d.m.Y H:i:s'),
                TextColumn::make('order_id')
                    ->label('№ заказа'),
                TextColumn::make('last_status_enum_id')
                    ->label('Статус')
                    ->getStateUsing(function (OnlinePayment $payment) {
                        return $payment->last_status_enum_id?->name();
                    }),
                TextColumn::make('admin.name')
                    ->label('Менеджер'),
                TextColumn::make('method_enum_id')
                    ->label('Способ оплаты')
                    ->getStateUsing(function (OnlinePayment $payment) {
                        return $payment->method_enum_id?->name();
                    }),
                TextColumn::make('amount')
                    ->label('Сумма платежа'),
                TextColumn::make('paid_amount')
                    ->label('Сумма оплаченная клиентом'),
                TextColumn::make('currency_code')
                    ->label('Код валюты'),
                TextColumn::make('expires_at')
                    ->label('Срок действия платежа')
                    ->dateTime('d.m.Y H:i:s'),
                TextColumn::make('link')
                    ->label('Ссылка на оплату')
                    ->getStateUsing(function (OnlinePayment $payment) {
                        return '<a href="' . $payment->link . '" target="_blank">Ссылка на станицу оплаты</a>';
                    })->html(),
            ]);
    }
}
