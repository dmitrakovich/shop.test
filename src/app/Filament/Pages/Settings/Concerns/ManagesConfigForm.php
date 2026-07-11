<?php

namespace App\Filament\Pages\Settings\Concerns;

use App\Models\Config;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
trait ManagesConfigForm
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    abstract protected static function configKey(): string;

    abstract protected function getSavedNotificationTitle(): string;

    public function mount(): void
    {
        $this->form->fill(Config::findCacheable(static::configKey()));
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Config::query()->updateOrCreate(
            ['key' => static::configKey()],
            ['config' => $data],
        );

        Notification::make()
            ->title($this->getSavedNotificationTitle())
            ->success()
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Сохранить')
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }
}
