<?php

namespace App\Providers;

use App\Enums\Filament\NavGroup;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Widgets\OrdersChart;
use App\Http\Controllers\Admin\PhpInfoController;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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
        ini_set('memory_limit', '1G');

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
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                OrdersChart::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups(NavGroup::class)
            ->navigationItems([
                ...$this->generateHorizonNavItems(),
                ...$this->generateOldAdminNavItems(),
            ])
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
                FilamentShieldPlugin::make()
                    ->navigationGroup(NavGroup::Management) // doesn't work: lib bug
                    ->registerNavigation(true),
            ])
            ->bootUsing(function (Panel $panel) {
                Table::configureUsing(function (Table $table) {
                    $table->paginated([10, 25, 50, 100, 200])
                        ->defaultSort('id', 'desc');
                });
            })
            ->assets([
                Css::make('custom', resource_path('css/custom-filament.css')),
            ], 'filament')
            ->authenticatedRoutes(function (Panel $panel): void {
                Route::get('phpinfo', PhpInfoController::class)->name('phpinfo');
            })
            ->spaUrlExceptions([
                '*/admin/phpinfo',
            ])
            ->spa();
    }

    /**
     * @return array<int, NavigationItem>
     */
    private function generateHorizonNavItems(): array
    {
        return [
            NavigationItem::make('Horizon')
                ->label('Очереди (Horizon)')
                ->icon(Heroicon::OutlinedServerStack)
                ->group(NavGroup::Management)
                ->url(fn (): string => route('horizon.index'))
                ->openUrlInNewTab()
                ->sort(5)
                ->visible(fn (): bool => $this->userCanSeeHorizonLink()),
        ];
    }

    private function userCanSeeHorizonLink(): bool
    {
        $user = auth()->user();

        if ($user === null) {
            return false;
        }

        return app()->environment('local') || Gate::check('viewHorizon', $user);
    }

    /**
     * Links to old admin sections that are not migrated yet, placed in their target groups.
     *
     * @return array<int, NavigationItem>
     */
    private function generateOldAdminNavItems(): array
    {
        return $this->getOldAdminNavItems()
            ->map(fn (array $item, string $uri): NavigationItem => NavigationItem::make($item['label'])
                ->url(url('old-admin/' . $uri), shouldOpenInNewTab: true)
                ->group($item['group'])
                ->sort($item['sort'])
                ->badge('old', color: 'gray')
                ->badgeTooltip('Откроется в старой админке'))
            ->values()
            ->all();
    }

    /**
     * @return Collection<string, array{label: string, group: NavGroup, sort: int}>
     */
    private function getOldAdminNavItems(): Collection
    {
        return collect([
            'orders' => ['label' => 'Заказы', 'group' => NavGroup::Orders, 'sort' => 1],
            'order-items' => ['label' => 'Товары в заказах', 'group' => NavGroup::Orders, 'sort' => 2],
            'orders/offline' => ['label' => 'Оффлайн продажи', 'group' => NavGroup::Orders, 'sort' => 4],
            'bookkeeping/payments' => ['label' => 'Платежи', 'group' => NavGroup::Orders, 'sort' => 5],
            'departures/order-to-send' => ['label' => 'Заказы на отправку', 'group' => NavGroup::Departures, 'sort' => 1],
            'departures/track-numbers' => ['label' => 'Трек-номера', 'group' => NavGroup::Departures, 'sort' => 3],
            'automation/stock' => ['label' => 'Склад', 'group' => NavGroup::Inventory, 'sort' => 1],
            'automation/inventory' => ['label' => 'Новые товары из 1С', 'group' => NavGroup::Inventory, 'sort' => 2],
        ]);
    }
}
