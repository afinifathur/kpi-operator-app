<?php

namespace App\Providers\Filament;

use App\Filament\Pages\AnomalyReport;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // Brand
            ->brandName('PERONI KARYA SENTRA')
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('2rem')

            // Theme (ikut System; dark mode diaktifkan)
            ->defaultThemeMode(ThemeMode::System)
            ->darkMode(true)
            ->viteTheme('resources/css/filament/admin/theme.css')

            // Warna primer
            ->colors([
                'primary' => [
                    50 => '#eef2ff',
                    100 => '#e0e7ff',
                    200 => '#c7d2fe',
                    300 => '#a5b4fc',
                    400 => '#818cf8',
                    500 => '#6366f1',
                    600 => '#4f46e5',
                    700 => '#4338ca',
                    800 => '#3730a3',
                    900 => '#312e81',
                ],
            ])

            // Urutan grup menu (QC tepat DI BAWAH HR)
            ->navigationGroups([
                NavigationGroup::make()->label('Dashboard')->collapsed(false),
                NavigationGroup::make()->label('Laporan'),
                NavigationGroup::make()->label('HR'),
                NavigationGroup::make()->label('QC'),
                NavigationGroup::make()->label('Master Data'),
                NavigationGroup::make()->label('Transaksi'),
                NavigationGroup::make()->label('Konfigurasi'),
            ])

            // Item navigasi kustom
            ->navigationItems([
                NavigationItem::make('QC database')
                    ->group('QC')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn () => route('admin.qc.index')) // /admin/qc
                    ->isActiveWhen(fn () => request()->routeIs('admin.qc.index'))
                    ->sort(700),

                NavigationItem::make('QC Import')
                    ->group('QC')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn () => route('admin.qc.import.create'))                 // <- perbaikan
                    ->isActiveWhen(fn () => request()->routeIs('admin.qc.import*')) // <- aktif utk GET & POST
                    ->sort(701),

                NavigationItem::make('QC Operators')
                    ->group('QC')
                    ->icon('heroicon-o-users')
                    ->url(fn () => route('admin.qc.operators.index'))
                    ->isActiveWhen(fn () => request()->routeIs('admin.qc.operators.*'))
                    ->sort(702),

                NavigationItem::make('QC KPI / Report')
                    ->group('QC')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn () => route('admin.qc.report.index'))
                    ->isActiveWhen(fn () => request()->routeIs('admin.qc.report*'))
                    ->sort(703),
            ])

            // Auto-discover
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                AnomalyReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])

            // Middleware
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
            ]);
    }

    public function boot(): void {}
}

