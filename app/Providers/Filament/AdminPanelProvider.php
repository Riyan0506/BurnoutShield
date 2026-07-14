<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
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
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            // Filament 3.2.0 punya dua bug tampilan saat Livewire me-morph DOM
            // (mis. apply filter tabel, mount action):
            //   1. Inline display:none milik Alpine pada elemen tersembunyi (backdrop
            //      modal, panel dropdown, bar "Select all") ikut tersapu, sehingga
            //      semuanya berkedip/tampil padahal seharusnya tertutup.
            //   2. Morph menambahkan ulang atribut x-ignore bawaan ax-load pada wrapper
            //      tabel, membuat Alpine menolak menginisialisasi subtree baru di
            //      dalamnya — modal konfirmasi & dropdown segar tak pernah hidup.
            // Hook 'morphed' berjalan sinkron sebelum browser paint untuk koreksi
            // visual, lalu sapuan async menginisialisasi subtree yang terlewat.
            ->renderHook('panels::body.end', fn (): HtmlString => new HtmlString(<<<'HTML'
                <script>
                    document.addEventListener('livewire:init', () => {
                        // Sembunyikan lagi elemen yang Alpine tandai tersembunyi tapi
                        // inline display:none-nya tersapu morph. Harus sinkron agar
                        // frame rusak tidak sempat ter-render.
                        const fixWipedVisibility = () => {
                            if (! window.Alpine) return;

                            // Elemen x-show / panel x-float menandai state di _x_isShown.
                            document.querySelectorAll('[x-show], .fi-dropdown-panel').forEach((el) => {
                                if (el._x_isShown === false) el.style.setProperty('display', 'none');
                            });

                            // Panel dropdown yang belum sempat diinisialisasi x-float
                            // (_x_isShown masih undefined) juga tidak boleh tampil.
                            document.querySelectorAll('.fi-dropdown-panel').forEach((el) => {
                                if (! el._x_isShown) el.style.setProperty('display', 'none');
                            });

                            // Backdrop modal tertutup (cek state komponen langsung).
                            document.querySelectorAll('.fi-modal').forEach((modal) => {
                                let state;

                                try { state = Alpine.$data(modal); } catch (e) { return; }

                                if (! state || state.isOpen !== false) return;

                                modal.querySelectorAll(':scope > div[x-show]').forEach((el) => {
                                    el.style.setProperty('display', 'none');
                                });
                            });
                        };

                        // Morph me-re-apply directive x-ignore bawaan ax-load pada wrapper
                        // (mis. tabel) yang komponennya sudah dimuat, meracuni _x_ignore
                        // sehingga Alpine menolak menginisialisasi elemen segar apa pun di
                        // dalamnya (modal, checkbox x-model, tombol). Setelah komponen
                        // termuat, kunci _x_ignore agar racun tidak bisa terpasang lagi —
                        // pipeline inisialisasi bawaan morph lalu bekerja normal.
                        const pinned = new WeakSet();

                        const pinLoadedLazyComponents = () => {
                            document.querySelectorAll('[ax-load]').forEach((el) => {
                                if (pinned.has(el) || ! el._x_dataStack) return;

                                el._x_ignore = false;
                                el.removeAttribute('x-ignore');
                                Object.defineProperty(el, '_x_ignore', {
                                    get: () => false,
                                    set: () => {},
                                    configurable: true,
                                });
                                pinned.add(el);
                            });
                        };

                        // Komponen ax-load dimuat async setelah page load — poll singkat
                        // sampai semuanya hidup lalu dikunci.
                        const pinPoll = setInterval(pinLoadedLazyComponents, 150);
                        setTimeout(() => clearInterval(pinPoll), 8000);

                        // Jaring pengaman untuk subtree modal yang terlanjur terlewat
                        // (x-data tanpa scope, x-ref container hilang, x-cloak basi).
                        const initFreshModalTrees = () => {
                            if (! window.Alpine) return;

                            document.querySelectorAll('.fi-modal [x-data]').forEach((el) => {
                                if (el._x_dataStack) return;

                                try { Alpine.initTree(el); } catch (e) { console.warn('morph-fix initTree:', e); }
                            });

                            document.querySelectorAll('.fi-modal').forEach((modal) => {
                                if (! modal._x_dataStack) return;

                                const container = modal.querySelector('[x-ref="modalContainer"]');

                                if (container) {
                                    modal._x_refs = modal._x_refs || {};
                                    modal._x_refs.modalContainer = container;
                                }
                            });

                            document.querySelectorAll('.fi-modal [x-cloak]').forEach((el) => {
                                const scope = el.hasAttribute('x-data') ? el : el.closest('[x-data]');

                                if (scope && scope._x_dataStack) el.removeAttribute('x-cloak');
                            });

                            fixWipedVisibility();
                        };

                        Livewire.hook('morphed', () => {
                            fixWipedVisibility();
                            queueMicrotask(() => {
                                pinLoadedLazyComponents();
                                initFreshModalTrees();
                            });
                            setTimeout(() => {
                                pinLoadedLazyComponents();
                                initFreshModalTrees();
                            }, 60);
                        });
                    });
                </script>
                HTML));
    }
}
