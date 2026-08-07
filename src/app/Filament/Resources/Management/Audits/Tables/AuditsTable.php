<?php

namespace App\Filament\Resources\Management\Audits\Tables;

use App\Enums\Audit\AuditEvent;
use App\Models\Audit;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Когда')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->label('Событие')
                    ->badge(),
                TextColumn::make('user')
                    ->label('Кто')
                    ->state(fn (Audit $record): ?string => $record->getUserLabel())
                    ->placeholder('Система / консоль')
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('user_id', $search)
                                ->orWhere('user_type', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('auditable')
                    ->label('Объект')
                    ->state(fn (Audit $record): string => $record->getAuditableLabel())
                    ->wrap()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->where('auditable_id', $search)
                                ->orWhere('auditable_type', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('changes')
                    ->label('Изменения')
                    ->state(function (Audit $record): string {
                        $attributes = $record->getChangedAttributeNames();

                        if ($attributes === []) {
                            return '—';
                        }

                        return Str::limit(implode(', ', $attributes), 60);
                    })
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tags')
                    ->label('Теги')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Событие')
                    ->options(AuditEvent::class)
                    ->native(false),
                SelectFilter::make('auditable_type')
                    ->label('Тип объекта')
                    ->options(fn (): array => Audit::query()
                        ->whereNotNull('auditable_type')
                        ->distinct()
                        ->orderBy('auditable_type')
                        ->pluck('auditable_type', 'auditable_type')
                        ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                        ->all())
                    ->searchable()
                    ->native(false),
                Filter::make('created_at')
                    ->schema([
                        Fieldset::make()
                            ->label('Дата')
                            ->schema([
                                DatePicker::make('created_from')
                                    ->label('с:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                                DatePicker::make('created_until')
                                    ->label('по:')
                                    ->native(false)
                                    ->closeOnDateSelection(),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if ($data['created_from'] ?? null) {
                            $query->whereDate('created_at', '>=', $data['created_from']);
                        }

                        if ($data['created_until'] ?? null) {
                            $query->whereDate('created_at', '<=', $data['created_until']);
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalHeading(fn (Audit $record): string => 'Аудит #' . $record->id),
            ])
            ->toolbarActions([]);
    }
}
