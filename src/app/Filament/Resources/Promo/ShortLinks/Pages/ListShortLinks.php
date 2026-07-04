<?php

namespace App\Filament\Resources\Promo\ShortLinks\Pages;

use App\Enums\Order\OrderMethod;
use App\Filament\Resources\Promo\ShortLinks\ShortLinkResource;
use App\Models\ShortLink;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use LogicException;

class ListShortLinks extends ListRecords
{
    protected static string $resource = ShortLinkResource::class;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        parent::mount();

        $this->getShortLinkForm()->fill([
            'init_link' => '',
            'source' => null,
            'out_link' => '',
            'generated_short_link' => '',
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Генератор коротких ссылок')
                    ->columns(2)
                    ->schema([
                        TextInput::make('init_link')
                            ->label('Исходная ссылка')
                            ->placeholder('https://barocco.by...')
                            ->url()
                            ->rules(['starts_with:https://barocco.by'])
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                $this->updateGeneratedLink($get, $set);
                            })
                            ->required()
                            ->columnSpanFull(),
                        Select::make('source')
                            ->label('Источник заказа')
                            ->options(OrderMethod::shortLinkSelectOptions())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set): void {
                                $this->updateGeneratedLink($get, $set);
                            }),
                        TextInput::make('out_link')
                            ->label('Сгенерированная ссылка')
                            ->placeholder('Сгенерированная ссылка')
                            ->readOnly(),
                        TextInput::make('generated_short_link')
                            ->label('Короткая ссылка')
                            ->readOnly()
                            ->copyable()
                            ->visible(fn (?string $state): bool => filled($state))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function createShortLink(): void
    {
        $form = $this->getShortLinkForm();
        $data = $form->getState();
        $outLink = $this->buildTrackedLink((string)$data['init_link'], is_string($data['source'] ?? null) ? $data['source'] : null);
        $shortLink = ShortLink::createShortLink($outLink);
        $shortUrl = $shortLink->publicUrl();

        $form->fill([
            ...$data,
            'out_link' => $outLink,
            'generated_short_link' => $shortUrl,
        ]);

        Notification::make()
            ->title('Короткая ссылка создана')
            ->body($shortUrl)
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('createShortLink')
                    ->footer([
                        Actions::make([
                            Action::make('createShortLink')
                                ->label('Сгенерировать короткую ссылку')
                                ->submit('createShortLink'),
                        ]),
                    ]),
                $this->getTabsContentComponent(),
                EmbeddedTable::make(),
            ]);
    }

    private function updateGeneratedLink(Get $get, Set $set): void
    {
        $initLink = $get('init_link');

        if (!is_string($initLink) || $initLink === '') {
            $set('out_link', '');

            return;
        }

        $source = $get('source');
        $set('out_link', $this->buildTrackedLink($initLink, is_string($source) ? $source : null));
        $set('generated_short_link', '');
    }

    private function buildTrackedLink(string $initLink, ?string $source): string
    {
        $url = parse_url($initLink);

        if (!isset($url['scheme'], $url['host'])) {
            return $initLink;
        }

        $query = [];
        parse_str($url['query'] ?? '', $query);
        unset(
            $query['utm_source'],
            $query['utm_medium'],
            $query['utm_campaign'],
            $query['utm_content'],
            $query['utm_term'],
        );

        $orderMethod = is_string($source) ? OrderMethod::tryFrom($source) : null;

        if ($orderMethod !== null) {
            [$utmSource, $utmMedium, $utmCampaign] = $orderMethod->utmSources();
            $query['utm_source'] = $utmSource;
            $query['utm_medium'] = $utmMedium;
            $query['utm_campaign'] = "{$utmCampaign}link";
        }

        $query['utm_content'] = $this->adminUsername();
        $query['utm_term'] = now()->format('ymd');

        $path = $url['path'] ?? '';
        $queryString = http_build_query($query);

        return "{$url['scheme']}://{$url['host']}{$path}" . ($queryString === '' ? '' : "?{$queryString}");
    }

    private function adminUsername(): string
    {
        $user = auth('admin')->user();

        return is_object($user) && property_exists($user, 'username') ? (string)$user->username : '';
    }

    private function getShortLinkForm(): Schema
    {
        $schema = $this->getSchema('form');

        if (!$schema instanceof Schema) {
            throw new LogicException('Short link form schema is not registered.');
        }

        return $schema;
    }
}
