<?php

namespace App\Providers;

use App\Filament\Pages\Auth\Login;
use Encore\Admin\Auth\Database\Menu as OldAdminMenu;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function register(): void
    {
        $rootPath = strtok(request()->path(), '/');
        if (in_array($rootPath, ['admin', 'livewire', 'filament'])) {
            parent::register();
        }
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                // 'promo' => NavigationGroup::make()
                //     ->label('Промо')
                //     ->icon('heroicon-o-currency-dollar'), // !!!
                'settings' => NavigationGroup::make()
                    ->label('Управление')
                    ->icon('heroicon-o-cog-6-tooth'),
                'old-admin-panel' => NavigationGroup::make()
                    ->label('Старая админка')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->collapsed(),
            ])
            ->navigationItems(
                OldAdminMenu::query()
                    ->whereNotIn('parent_id', [0, 2, 44])
                    ->orderBy('order')
                    ->get(['id', 'title', 'uri'])
                    ->map(
                        fn (OldAdminMenu $menu) => NavigationItem::make($menu->id)
                            ->label($menu->title)
                            ->url(url('admin/' . $menu->uri), shouldOpenInNewTab: false)
                            ->group('old-admin-panel')
                    )
                    ->toArray()
            )
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
            // ->spa()
        ;
    }
}
