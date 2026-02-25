<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->brandName('نظام ادارة تيجان العلم')
            ->favicon(asset('images/logo.png'))
            ->globalSearch(false)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                \App\Http\Middleware\LoadEmployeePermissions::class,
            ]);
    }
    
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => Blade::render(<<<'HTML'
                <style>
                    * {
                        direction: rtl !important;
                    }
                    html, body {
                        direction: rtl !important;
                        text-align: right !important;
                    }
                    /* Keep switches sane in RTL */
                    :where(.fi-fo-toggle),
                    :where(.fi-fo-toggle *) {
                        direction: ltr !important;
                    }
                    .fi-topbar,
                    .fi-sidebar-nav {
                        direction: rtl !important;
                    }
                    input,
                    textarea,
                    select {
                        text-align: right !important;
                    }
                    .fi-topbar-sidebar-toggle {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.35rem;
                    }
                    .fi-topbar-sidebar-toggle .fi-icon-btn {
                        transition: opacity 0.2s ease, transform 0.2s ease;
                    }
                    .fi-sidebar {
                        transition: transform 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
                    }
                    .fi-sidebar-close-overlay {
                        transition: opacity 0.3s ease;
                    }
                    .fi-main-ctn {
                        transition: margin-inline 0.3s ease;
                    }
                    .fi-sidebar-item-label-with-marker {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.35rem;
                    }
                    .fi-sidebar-item-last-marker {
                        display: inline-flex;
                        align-items: center;
                        opacity: 0.8;
                        transition: opacity 0.2s ease;
                    }
                    .fi-sidebar-item:hover .fi-sidebar-item-last-marker {
                        opacity: 1;
                    }
                    @media (max-width: 1024px) {
                        .fi-sidebar {
                            position: fixed !important;
                            inset-block: 0;
                            inset-inline-end: 0;
                            max-width: min(20rem, 90vw);
                            width: 100%;
                            transform: translateX(100%);
                            opacity: 0;
                            visibility: hidden;
                            pointer-events: none;
                        }
                        .fi-sidebar.fi-sidebar-open {
                            transform: translateX(0);
                            opacity: 1;
                            visibility: visible;
                            pointer-events: auto;
                        }
                        html:not([dir='rtl']) .fi-sidebar {
                            inset-inline-start: 0;
                            inset-inline-end: auto;
                            transform: translateX(-100%);
                        }
                        html:not([dir='rtl']) .fi-sidebar.fi-sidebar-open {
                            transform: translateX(0);
                        }
                        .fi-layout {
                            grid-template-columns: 1fr !important;
                        }
                        .fi-main-ctn {
                            margin-inline: 0 !important;
                        }
                        .fi-topbar-item-dropdown-panel,
                        [role="menu"] {
                            display: block !important;
                            position: fixed !important;
                            background: white !important;
                            z-index: 9999 !important;
                            max-height: 80vh !important;
                            overflow-y: auto !important;
                            inset-inline: 0;
                            margin-inline: auto;
                        }
                    }
                </style>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
                <style>
                    * {
                        font-family: 'Tajawal', sans-serif !important;
                    }
                </style>
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
            HTML),
        );
    }
}
