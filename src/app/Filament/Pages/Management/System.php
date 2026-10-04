<?php

namespace App\Filament\Pages\Management;

use App\Enums\Filament\NavGroup;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

use function Sentry\captureException;

class System extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = NavGroup::Management;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?string $navigationLabel = 'Система';

    protected static ?string $title = 'Система';

    protected static ?string $slug = 'system';

    protected static ?int $navigationSort = 6;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Text::make('Полный phpinfo открывается в новой вкладке — по нему можно сверять настройки PHP на проде.'),
            ]);
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('clearCache')
                ->label('Сбросить кэш')
                ->icon(Heroicon::OutlinedArrowPath)
                ->requiresConfirmation()
                ->modalHeading('Сбросить кэш')
                ->modalDescription('Будут очищены кэш приложения, конфиг, маршруты, представления и события.')
                ->modalSubmitActionLabel('Сбросить')
                ->action(function (): void {
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Artisan::call('event:clear');

                    Notification::make()
                        ->title('Кэш сброшен')
                        ->success()
                        ->send();
                }),
            Action::make('testSentry')
                ->label('Проверить Sentry')
                ->icon(Heroicon::OutlinedBugAnt)
                ->requiresConfirmation()
                ->modalHeading('Отправить тестовую ошибку в Sentry')
                ->modalSubmitActionLabel('Отправить')
                ->action(function (): void {
                    captureException(new Exception('Debug Sentry error!'));

                    Notification::make()
                        ->title('Тестовая ошибка отправлена в Sentry')
                        ->success()
                        ->send();
                }),
            Action::make('phpinfo')
                ->label('PHP Info')
                ->icon(Heroicon::OutlinedInformationCircle)
                ->url(fn (): string => route('filament.admin.phpinfo'), shouldOpenInNewTab: true),
        ];
    }
}
