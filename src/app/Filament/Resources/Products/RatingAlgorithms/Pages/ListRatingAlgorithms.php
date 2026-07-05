<?php

namespace App\Filament\Resources\Products\RatingAlgorithms\Pages;

use App\Filament\Resources\Products\RatingAlgorithms\RatingAlgorithmResource;
use App\Jobs\UpdateProductsRatingJob;
use App\Models\Config;
use App\Models\RatingAlgorithm;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRatingAlgorithms extends ListRecords
{
    protected static string $resource = RatingAlgorithmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->settingsAction(),
            $this->recalculateAction(),
            CreateAction::make(),
        ];
    }

    private function settingsAction(): Action
    {
        return Action::make('settings')
            ->label('Настройки рейтинга')
            ->modalWidth('lg')
            ->fillForm(fn (): array => self::ratingConfig())
            ->form([
                Select::make('popularity_algorithm_id')
                    ->label('Алгоритм для популярности')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('newness_algorithm_id')
                    ->label('Алгоритм для новинок')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('season_algorithm_id')
                    ->label('Алгоритм для актуального сезона')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
                Select::make('sale_algorithm_id')
                    ->label('Алгоритм для SALE')
                    ->options(fn () => RatingAlgorithm::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->required(),
            ])
            ->action(function (array $data): void {
                Config::query()->updateOrCreate(
                    ['key' => 'rating'],
                    ['config' => self::normalizeConfig([...self::ratingConfig(), ...$data])]
                );

                Notification::make()
                    ->title('Настройки рейтинга сохранены')
                    ->success()
                    ->send();
            });
    }

    private function recalculateAction(): Action
    {
        return Action::make('recalculate')
            ->label('Пересчитать рейтинг')
            ->requiresConfirmation()
            ->action(function (): void {
                UpdateProductsRatingJob::dispatchSync();

                Notification::make()
                    ->title('Рейтинг пересчитан')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, mixed>
     */
    private static function ratingConfig(): array
    {
        return self::normalizeConfig(Config::findCacheable('rating'));
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private static function normalizeConfig(array $config): array
    {
        return [
            'popularity_algorithm_id' => isset($config['popularity_algorithm_id']) ? (int)$config['popularity_algorithm_id'] : null,
            'newness_algorithm_id' => isset($config['newness_algorithm_id']) ? (int)$config['newness_algorithm_id'] : null,
            'season_algorithm_id' => isset($config['season_algorithm_id']) ? (int)$config['season_algorithm_id'] : null,
            'sale_algorithm_id' => isset($config['sale_algorithm_id']) ? (int)$config['sale_algorithm_id'] : null,
            'last_update' => $config['last_update'] ?? null,
        ];
    }
}
