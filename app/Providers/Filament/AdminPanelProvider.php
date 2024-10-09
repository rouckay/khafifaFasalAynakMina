<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use App\Models\Numeraha;
use Filament\PanelProvider;
use App\Filament\Pages\Backups;
use Filament\Support\Colors\Color;
use Awcodes\Overlook\OverlookPlugin;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Awcodes\Overlook\Widgets\OverlookWidget;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\FontProviders\SpatieGoogleFontProvider;
use Guava\FilamentKnowledgeBase\KnowledgeBasePlugin;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use \Okeonline\FilamentArchivable\FilamentArchivablePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            // ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                NavigationItem::make('my-profile')
                    ->icon('heroicon-m-user')
                    ->label('زما پروفایل')
                    ->url(env('APP_URL') . '/admin/my-profile')
                    ->sort(10)
                    ->group('ډیټابیس مدیریان'),
                NavigationItem::make('Selled_numeraha')
                    ->icon('heroicon-m-map')
                    ->label('د پلورل شویو نمرو لیست')
                    ->isActiveWhen(fn() => request()->routeIs('filament.admin.resources.numerahas.index'))
                    ->url(env('APP_URL') . 'admin/numerahas?tableFilters[%D9%BE%D9%84%D9%88%D8%B1%D9%84%20%D8%B4%D9%88%DB%8C%20%D9%86%D9%85%D8%B1%DB%8C%20(%DA%81%D9%85%DA%A9%DB%8C)][value]=1')
                    ->sort(2)
                    ->badge(function () {
                        // Replace the following with the actual logic to get the badge count
                        return Numeraha::query()->whereHas('customers')->count();

                    }),
                NavigationItem::make('remaining_numeraha')
                    ->icon('heroicon-o-map')
                    ->label('د پاتی نمرو لیست')
                    ->isActiveWhen(fn() => request()->routeIs('filament.admin.resources.numerahas.index'))
                    ->url(env('APP_URL') . 'admin/numerahas?tableFilters[%D9%BE%D9%84%D9%88%D8%B1%D9%84%20%D8%B4%D9%88%DB%8C%20%D9%86%D9%85%D8%B1%DB%8C%20(%DA%81%D9%85%DA%A9%DB%8C)][value]=0')
                    ->sort(3)
                    ->badge(function () {
                        return Numeraha::query()->whereDoesntHave('customers')->count();
                    }),
            ])->navigationGroups([
                    'د مشتریانو مدیریت' => NavigationGroup::make(fn() => __('د مشتریانو مدیریت')),
                    'مالی مدیریت' => NavigationGroup::make(fn() => __('مالی مدیریت')),
                    'ډیټابیس مدیریان' => NavigationGroup::make(fn() => __('ډیټابیس مدیریان')),
                    'Settings' => NavigationGroup::make(fn() => __('تنظیمات')),
                ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->slug('my-profile')
                    ->setTitle('My Profile')
                    ->setNavigationLabel('My Profile')
                    ->setNavigationGroup('Group Profile')
                    ->setIcon('heroicon-o-user')
                    ->setSort(10)
                    ->canAccess(fn() => auth()->user()->id === 1)
                    ->shouldRegisterNavigation(false)
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowSanctumTokens()
                    ->shouldShowBrowserSessionsForm()
                    ->shouldShowAvatarForm()
                    ->shouldShowSanctumTokens(
                        condition: fn() => auth()->user()->id === 1, //optional
                    )->shouldShowBrowserSessionsForm(
                        fn() => auth()->user()->id === 1, //optional
                        //OR
                        false //optional
                    ),
                \Okeonline\FilamentArchivable\FilamentArchivablePlugin::make(),
                SpotlightPlugin::make(),
                FilamentSpatieLaravelBackupPlugin::make()
                    // ->usingPage(Backups::class)
                    ->usingPolingInterval('10s')
                    ->timeout(120),
                \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make(),
                \TomatoPHP\FilamentSettingsHub\FilamentSettingsHubPlugin::make(),
                \Hasnayeen\Themes\ThemesPlugin::make(),
                KnowledgeBasePlugin::make(),
            ])
            ->databaseNotifications()
            ->resources([
                config('filament-logger.activity_resource')
            ])->font('Inter', provider: SpatieGoogleFontProvider::class);
    }
}
