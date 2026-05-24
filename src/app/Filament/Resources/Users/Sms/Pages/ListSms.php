<?php

namespace App\Filament\Resources\Users\Sms\Pages;

use App\Enums\Sms\SmsRoute;
use App\Filament\Resources\Users\Sms\SmsResource;
use App\Models\Config;
use App\Services\LogService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Notifications\Facades\SmsTraffic;
use Throwable;

class ListSms extends ListRecords
{
    protected static string $resource = SmsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->sendSmsAction(),
            $this->settingsAction(),
        ];
    }

    private function sendSmsAction(): Action
    {
        return Action::make('sendSms')
            ->label('Отправить SMS')
            ->modalHeading('Отправить Vb/SMS')
            ->modalSubmitActionLabel('Отправить')
            ->fillForm([
                'phone' => '',
                'text' => '',
                'route' => SmsRoute::default()->value,
            ])
            ->form([
                TextInput::make('phone')
                    ->label('Номер телефона')
                    ->tel()
                    ->maxLength(20)
                    ->required(),
                Select::make('route')
                    ->label('Тип отправки')
                    ->options(SmsRoute::class)
                    ->default(SmsRoute::default()->value)
                    ->native(false)
                    ->required(),
                Textarea::make('text')
                    ->label('Текст сообщения')
                    ->maxLength(500)
                    ->rows(6)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->action(function (array $data): void {
                $phone = (string)$data['phone'];
                $text = (string)$data['text'];
                $route = (string)$data['route'];
                $adminId = auth('admin')->id();
                $logService = app(LogService::class);

                try {
                    $response = SmsTraffic::send($phone, $text, ['route' => $route]);
                } catch (Throwable $exception) {
                    report($exception);

                    $logService->logSms(
                        phone: $phone,
                        text: $text,
                        route: $route,
                        adminId: is_int($adminId) ? $adminId : null,
                        status: 'Ошибка отправки: ' . $exception->getMessage(),
                    );

                    Notification::make()
                        ->title('Сообщение не отправлено')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $logService->logSms(
                    phone: $phone,
                    text: $text,
                    route: $route,
                    adminId: is_int($adminId) ? $adminId : null,
                    status: $response->getDescription()
                );

                Notification::make()
                    ->title('Сообщение отправлено')
                    ->body('Id сообщения: ' . $response->getSmsId())
                    ->success()
                    ->send();
            });
    }

    private function settingsAction(): Action
    {
        return Action::make('settings')
            ->label('Настройки')
            ->modalHeading('SMS настройки')
            ->modalSubmitActionLabel('Сохранить')
            ->fillForm(fn (): array => [
                'enabled' => ($this->smsConfig()['enabled'] ?? 'off') === 'on',
            ])
            ->form([
                Toggle::make('enabled')
                    ->label('Включено'),
            ])
            ->action(function (array $data): void {
                Config::query()->updateOrCreate(
                    ['key' => 'sms'],
                    ['config' => ['enabled' => $data['enabled'] ? 'on' : 'off']]
                );

                Notification::make()
                    ->title('SMS настройки сохранены')
                    ->success()
                    ->send();
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function smsConfig(): array
    {
        $config = Config::query()->find('sms');

        return $config instanceof Config ? $config->config : [];
    }
}
