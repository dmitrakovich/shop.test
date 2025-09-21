<?php

namespace App\Providers;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Filament\Widgets;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use FilipFonal\FilamentLogManager\FilamentLogManager;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Collection;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        $rootPath = strtok(request()->path(), '/');
        if (in_array($rootPath, ['api', 'cart', 'catalog'])) {
            return;
        }

        parent::register();
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(Width::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'promo' => NavigationGroup::make()
                    ->label('Промо')
                    ->icon('heroicon-o-fire'),
                'user' => NavigationGroup::make()
                    ->label('Клиенты')
                    ->icon('heroicon-o-user-group'),
                'products' => NavigationGroup::make()
                    ->label('Товары')
                    ->icon('heroicon-o-squares-2x2'),
                'registries' => NavigationGroup::make()
                    ->label('Реестры')
                    ->icon('heroicon-o-folder'),
                'old-admin-panel' => NavigationGroup::make()
                    ->label('Старая админка')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->collapsed(),
                'automation' => NavigationGroup::make()
                    ->label('Автоматизация')
                    ->icon('heroicon-o-cog-8-tooth')
                    ->collapsed(),
                'management' => NavigationGroup::make()
                    ->label('Управление')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->navigationItems($this->generateOldAdminNavItems())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('admin')
            ->plugins([
                FilamentShieldPlugin::make(),
                // FilamentLogManager::make(),
            ])
            ->bootUsing(function (Panel $panel) {
                Table::configureUsing(function (Table $table) {
                    $table->paginated([10, 25, 50, 100, 200]);
                });
            })
            ->assets([
                Css::make('custom', resource_path('css/custom-filament.css')),
            ], 'filament')
            ->spa();
    }

    public function generateOldAdminNavItems(): array
    {
        return $this->getOldAdminNavItems()->map(function ($label, $uri) {
            return NavigationItem::make()
                ->label($label)
                ->url(url('admin/' . $uri), shouldOpenInNewTab: true)
                ->group('old-admin-panel');
        })->toArray();
    }

    private function getOldAdminNavItems(): Collection
    {
        return collect([
            'orders' => 'Заказы',
            'order-items' => 'Товары в заказах',
            'products' => 'Товары',
            'logs/inventory' => 'Наличие - лог',
            'automation/inventory' => 'Наличие - добавить',
            'automation/stock' => 'Склад',
        ]);
    }
}
