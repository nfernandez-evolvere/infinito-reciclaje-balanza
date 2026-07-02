<x-layouts.landing title="Sistema de Balanza">

    <x-slot:head>
        <style>
            .reveal {
                opacity: 0;
                transform: translateY(22px);
                transition: opacity 0.65s cubic-bezier(0.4, 0, 0.2, 1),
                            transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .reveal.visible {
                opacity: 1;
                transform: none;
            }
            @media (prefers-reduced-motion: reduce) {
                .reveal { opacity: 1; transform: none; transition: none; }
                .landing-bubble { animation: none !important; }
            }
            @keyframes lnd-hero-a {
                0%, 100% { transform: translateX(-50%) translateY(0); }
                50%       { transform: translateX(-50%) translateY(-16px); }
            }
            @keyframes lnd-hero-b {
                0%, 100% { transform: translateY(-50%); }
                50%       { transform: translate(-12px, calc(-50% - 14px)); }
            }
            @keyframes lnd-hero-c {
                0%, 100% { transform: translate(50%, -50%); }
                50%       { transform: translate(calc(50% + 10px), calc(-50% - 18px)); }
            }
            @keyframes lnd-hero-d {
                0%, 100% { transform: translate(0, 0); }
                50%       { transform: translate(14px, -18px); }
            }
            @keyframes lnd-hero-e {
                0%, 100% { transform: translate(0, 0); }
                50%       { transform: translate(-10px, 14px); }
            }
            @keyframes lnd-cta-a {
                0%, 100% { transform: translate(0, 0); }
                50%       { transform: translate(10px, -20px); }
            }
            @keyframes lnd-cta-b {
                0%, 100% { transform: translate(0, 0); }
                50%       { transform: translate(-12px, 16px); }
            }
            @keyframes lnd-cta-c {
                0%, 100% { transform: translate(50%, -50%); }
                50%       { transform: translate(calc(50% + 8px), calc(-50% - 14px)); }
            }
            @keyframes lnd-bg-a {
                0%   { transform: translate(0, 0); }
                33%  { transform: translate(24px, -30px); }
                66%  { transform: translate(-18px, 20px); }
                100% { transform: translate(0, 0); }
            }
            @keyframes lnd-bg-b {
                0%   { transform: translate(0, 0); }
                33%  { transform: translate(-22px, 26px); }
                66%  { transform: translate(16px, -22px); }
                100% { transform: translate(0, 0); }
            }
        </style>
    </x-slot:head>

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur-sm">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-2.5">
                <x-brand-logo class="size-7" />
                <span class="text-sm font-semibold">Infinito Reciclaje</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.button variant="ghost" size="icon" @click="$store.theme.toggle()" aria-label="Cambiar tema">
                    <x-lucide-sun x-show="!$store.theme.dark" class="size-4" />
                    <x-lucide-moon x-show="$store.theme.dark" x-cloak class="size-4" />
                </x-ui.button>
                <x-ui.button href="{{ route('login') }}">
                    Ingresar
                    <x-lucide-arrow-right class="size-3.5" />
                </x-ui.button>
            </div>
        </div>
    </header>

    <main>

        {{-- Herooo --}}
        <section class="relative overflow-hidden py-20 md:py-28">

            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-40 left-1/2 size-160 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-a 7s ease-in-out infinite;"></div>
                <div class="absolute bottom-0 left-1/4 size-80 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-b 9s ease-in-out infinite; animation-delay: -3s;"></div>
                <div class="absolute right-0 top-1/2 size-64 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-c 5s ease-in-out infinite; animation-delay: -2s;"></div>
                <div class="absolute top-1/3 -left-10 size-44 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-d 4s ease-in-out infinite; animation-delay: -1s;"></div>
                <div class="absolute bottom-1/4 right-1/3 size-32 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-e 6s ease-in-out infinite; animation-delay: -2s;"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 sm:px-6">

                <div class="mx-auto max-w-3xl text-center">
                    <span class="text-overline reveal">Sistema de Gestión de Balanza</span>
                    <h1 class="mt-4 text-balance text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl reveal delay-100">
                        Control del reciclaje,<br class="hidden sm:block"> sin planilla.
                    </h1>
                    <p class="mx-auto mt-6 max-w-xl text-balance text-lg text-muted-foreground reveal delay-200">
                        Registro ágil de pesajes, historial trazable y métricas en tiempo real para el predio de disposición final de la Municipalidad de Corrientes.
                    </p>
                    <div class="mt-8 flex justify-center reveal delay-300">
                        <x-ui.button size="lg" href="{{ route('login') }}">
                            Ingresar al sistema
                            <x-lucide-arrow-right />
                        </x-ui.button>
                    </div>
                    <p class="mt-5 text-xs text-muted-foreground reveal delay-300">Infinito Reciclaje × Municipalidad de Corrientes</p>
                </div>

                {{-- Hero screenshot --}}
                <div class="group relative mt-14 cursor-zoom-in overflow-hidden rounded-xl shadow-2xl ring-1 ring-foreground/10 reveal delay-500"
                     @click="$store.lightbox.show($store.theme.dark ? '/assets/dashboard-2-desktop-dark.png' : '/assets/dashboard-2-desktop-light.png', 'Dashboard')">
                    <img class="block w-full dark:hidden" src="/assets/dashboard-2-desktop-light.png" alt="Dashboard" />
                    <img class="hidden w-full dark:block" src="/assets/dashboard-2-desktop-dark.png" alt="Dashboard" />
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-foreground/0 transition-colors duration-200 group-hover:bg-foreground/5">
                        <div class="flex size-10 items-center justify-center rounded-full bg-background/80 opacity-0 shadow-lg backdrop-blur-sm transition-opacity duration-200 group-hover:opacity-100">
                            <x-lucide-zoom-in class="size-5" />
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- Features --}}
        <section class="relative overflow-hidden py-16 md:py-24">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-24 right-1/4 size-72 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-bg-a 8s ease-in-out infinite; animation-delay: -2s;"></div>
                <div class="absolute top-1/2 -left-20 size-80 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-bg-b 11s ease-in-out infinite; animation-delay: -5s;"></div>
                <div class="absolute -bottom-16 right-1/3 size-56 rounded-full bg-primary/5 landing-bubble"
                     style="animation: lnd-hero-e 7s ease-in-out infinite; animation-delay: -3s;"></div>
            </div>
            <div class="mx-auto max-w-6xl space-y-24 px-4 sm:px-6">

                {{-- Feature 1: Balanza --}}
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                    <div class="reveal">
                        <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-scale class="size-5" />
                        </div>
                        <h2 class="text-3xl font-semibold tracking-tight">Pesaje en segundos</h2>
                        <p class="mt-4 text-muted-foreground">
                            El operador identifica el vehículo por patente o número interno. El sistema autocompleta tara, titular y origen desde el padrón. Solo queda ingresar el peso bruto.
                        </p>
                        <ul class="mt-6 space-y-2.5 text-sm text-muted-foreground">
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Identificación por patente o número interno
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Cálculo automático de kg netos
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Alerta si el peso está fuera del rango habitual
                            </li>
                        </ul>
                    </div>

                    <div class="relative pb-4 reveal delay-150">
                        <div class="group relative cursor-zoom-in overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5"
                             @click="$store.lightbox.show($store.theme.dark ? '/assets/pesajes-desktop-dark.png' : '/assets/pesajes-desktop-light.png', 'Registro de pesaje')">
                            <img class="block w-full dark:hidden" src="/assets/pesajes-desktop-light.png" alt="Registro de pesaje" />
                            <img class="hidden w-full dark:block" src="/assets/pesajes-desktop-dark.png" alt="Registro de pesaje" />
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-foreground/0 transition-colors duration-200 group-hover:bg-foreground/5">
                                <div class="flex size-10 items-center justify-center rounded-full bg-background/80 opacity-0 shadow-lg backdrop-blur-sm transition-opacity duration-200 group-hover:opacity-100">
                                    <x-lucide-zoom-in class="size-5" />
                                </div>
                            </div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-[22%] overflow-hidden rounded-xl shadow-2xl ring-2 ring-background">
                            <img class="block w-full dark:hidden" src="/assets/pesajes-mobile-light.png" alt="Pesaje mobile" />
                            <img class="hidden w-full dark:block" src="/assets/pesajes-mobile-dark.png" alt="Pesaje mobile" />
                        </div>
                    </div>

                </div>

                {{-- Feature 2: Historial --}}
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                    <div class="group relative order-last lg:order-first cursor-zoom-in overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5 reveal"
                         @click="$store.lightbox.show($store.theme.dark ? '/assets/historia-pesajes-dark.png' : '/assets/historia-pesajes-light.png', 'Historial de pesajes')">
                        <img class="block w-full dark:hidden" src="/assets/historia-pesajes-light.png" alt="Historial de pesajes" />
                        <img class="hidden w-full dark:block" src="/assets/historia-pesajes-dark.png" alt="Historial de pesajes" />
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-foreground/0 transition-colors duration-200 group-hover:bg-foreground/5">
                            <div class="flex size-10 items-center justify-center rounded-full bg-background/80 opacity-0 shadow-lg backdrop-blur-sm transition-opacity duration-200 group-hover:opacity-100">
                                <x-lucide-zoom-in class="size-5" />
                            </div>
                        </div>
                    </div>

                    <div class="order-first lg:order-last reveal delay-150">
                        <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-clipboard-list class="size-5" />
                        </div>
                        <h2 class="text-3xl font-semibold tracking-tight">Cada pesaje, registrado</h2>
                        <p class="mt-4 text-muted-foreground">
                            Historial completo de ingresos y egresos. Filtrá por fecha, turno, vehículo o servicio para encontrar cualquier registro al instante.
                        </p>
                        <ul class="mt-6 space-y-2.5 text-sm text-muted-foreground">
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Filtros por fecha, turno y servicio
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Estado de cada camión en tiempo real
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                KPIs del período seleccionado
                            </li>
                        </ul>
                    </div>

                </div>

                {{-- Feature 3: Dashboard --}}
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                    <div class="reveal">
                        <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-bar-chart-2 class="size-5" />
                        </div>
                        <h2 class="text-3xl font-semibold tracking-tight">Métricas en tiempo real</h2>
                        <p class="mt-4 text-muted-foreground">
                            El administrador tiene visibilidad total: toneladas del día, distribución por tipo de vehículo y por zona, evolución mensual y anomalías detectadas automáticamente.
                        </p>
                        <ul class="mt-6 space-y-2.5 text-sm text-muted-foreground">
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                KPIs del día y del mes
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Distribución por tipo de vehículo y zona
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Detección automática de anomalías
                            </li>
                        </ul>
                    </div>

                    <div class="relative pb-4 reveal delay-150">
                        <div class="group relative cursor-zoom-in overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5"
                             @click="$store.lightbox.show($store.theme.dark ? '/assets/dashboard-desktop-dark.png' : '/assets/dashboard-desktop-light.png', 'Dashboard')">
                            <img class="block w-full dark:hidden" src="/assets/dashboard-desktop-light.png" alt="Dashboard" />
                            <img class="hidden w-full dark:block" src="/assets/dashboard-desktop-dark.png" alt="Dashboard" />
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-foreground/0 transition-colors duration-200 group-hover:bg-foreground/5">
                                <div class="flex size-10 items-center justify-center rounded-full bg-background/80 opacity-0 shadow-lg backdrop-blur-sm transition-opacity duration-200 group-hover:opacity-100">
                                    <x-lucide-zoom-in class="size-5" />
                                </div>
                            </div>
                        </div>
                        <div class="absolute bottom-0 right-0 w-[22%] overflow-hidden rounded-xl shadow-2xl ring-2 ring-background">
                            <img class="block w-full dark:hidden" src="/assets/dashboard-mobile-light.png" alt="Dashboard mobile" />
                            <img class="hidden w-full dark:block" src="/assets/dashboard-mobile-dark.png" alt="Dashboard mobile" />
                        </div>
                    </div>

                </div>

                {{-- Feature 4: Reportes y Alertas --}}
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                    <div class="space-y-3 reveal"
                         x-data="{
                             current: 0,
                             slides: [
                                 { src: '/assets/reportes-pesajes-1.png', alt: 'Portada — Informe de Pesajes', label: 'Informe de Pesajes' },
                                 { src: '/assets/reportes-pesajes-2.png', alt: 'Evolución diaria de toneladas',  label: 'Informe de Pesajes' },
                                 { src: '/assets/reportes-pesajes-3.png', alt: 'Distribución por zona',          label: 'Informe de Pesajes' },
                                 { src: '/assets/reportes-alertas-1.png', alt: 'Portada — Reporte de Alertas',  label: 'Reporte de Alertas' },
                                 { src: '/assets/reportes-alertas-2.png', alt: 'Alertas del período',           label: 'Reporte de Alertas' },
                             ],
                             prev() { this.current = (this.current - 1 + this.slides.length) % this.slides.length },
                             next() { this.current = (this.current + 1) % this.slides.length },
                         }">

                        <p class="text-xs font-medium text-muted-foreground" x-text="slides[current].label"></p>

                        <div class="relative overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5">
                            <div class="flex transition-transform duration-500 ease-in-out"
                                 :style="`transform: translateX(-${current * 100}%)`">
                                <template x-for="(slide, i) in slides" :key="i">
                                    <div class="group relative w-full shrink-0">
                                        <img :src="slide.src" :alt="slide.alt"
                                             class="block w-full cursor-zoom-in"
                                             @click="$store.lightbox.show(slide.src, slide.alt)" />
                                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-foreground/0 transition-colors duration-200 group-hover:bg-foreground/5">
                                            <div class="flex size-10 items-center justify-center rounded-full bg-background/80 opacity-0 shadow-lg backdrop-blur-sm transition-opacity duration-200 group-hover:opacity-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <button @click="prev()"
                                    class="absolute left-2 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 shadow-md ring-1 ring-border backdrop-blur-sm transition-colors hover:bg-background">
                                <x-lucide-chevron-left class="size-4" />
                            </button>
                            <button @click="next()"
                                    class="absolute right-2 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 shadow-md ring-1 ring-border backdrop-blur-sm transition-colors hover:bg-background">
                                <x-lucide-chevron-right class="size-4" />
                            </button>
                        </div>

                        <div class="flex items-center justify-center gap-1.5">
                            <template x-for="(slide, i) in slides" :key="i">
                                <button @click="current = i"
                                        :class="current === i ? 'w-4 bg-primary' : 'w-1.5 bg-muted-foreground/30'"
                                        class="h-1.5 rounded-full transition-all duration-300"></button>
                            </template>
                        </div>

                    </div>

                    <div class="reveal delay-150">
                        <div class="mb-4 flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-lucide-file-text class="size-5" />
                        </div>
                        <h2 class="text-3xl font-semibold tracking-tight">Informes que se generan solos</h2>
                        <p class="mt-4 text-muted-foreground">
                            El sistema produce reportes PDF mensuales de pesajes y alertas. Portada institucional, evolución diaria de toneladas, distribución por zona y detección de anomalías — listos para enviar o presentar a la gestión municipal.
                        </p>
                        <ul class="mt-6 space-y-2.5 text-sm text-muted-foreground">
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Informe mensual con evolución diaria y distribución territorial
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                Reporte de alertas clasificadas por tipo de anomalía
                            </li>
                            <li class="flex items-center gap-2.5">
                                <x-lucide-check class="size-4 shrink-0 text-primary" />
                                PDF con branding institucional, listo en segundos
                            </li>
                        </ul>
                    </div>

                </div>

            </div>
        </section>

        {{-- CTA final --}}
        <section class="relative overflow-hidden bg-primary py-16">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-16 -right-16 size-64 rounded-full bg-white/5 landing-bubble"
                     style="animation: lnd-cta-a 4s ease-in-out infinite;"></div>
                <div class="absolute -bottom-16 -left-16 size-72 rounded-full bg-white/5 landing-bubble"
                     style="animation: lnd-cta-b 6s ease-in-out infinite; animation-delay: -2s;"></div>
                <div class="absolute top-1/2 right-0 size-36 rounded-full bg-white/5 landing-bubble"
                     style="animation: lnd-cta-c 3s ease-in-out infinite; animation-delay: -1s;"></div>
            </div>
            <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 reveal">
                <h2 class="text-3xl font-bold tracking-tight text-primary-foreground">Listo para digitalizar la operación</h2>
                <p class="mt-4 text-lg text-primary-foreground/70">
                    Ingresá al sistema y empezá a registrar pesajes hoy.
                </p>
                <div class="mt-8">
                    <x-ui.button variant="secondary" size="lg" href="{{ route('login') }}">
                        Ingresar al sistema
                        <x-lucide-arrow-right />
                    </x-ui.button>
                </div>
            </div>
        </section>

        {{-- Lightbox --}}
        <div x-show="$store.lightbox.open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="$store.lightbox.hide()"
             @keydown.escape.window="$store.lightbox.hide()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
             x-cloak>
            <div class="relative">
                <img :src="$store.lightbox.src" :alt="$store.lightbox.alt"
                     class="max-h-[90vh] max-w-[90vw] rounded-lg object-contain shadow-2xl" />
                <button @click="$store.lightbox.hide()"
                        class="absolute -right-3 -top-3 flex size-8 items-center justify-center rounded-full bg-background shadow-md ring-1 ring-border transition-colors hover:bg-muted">
                    <x-lucide-x class="size-4" />
                </button>
            </div>
        </div>

    </main>

    {{-- Footer --}}
    <footer class="border-t border-border py-6">
        <div class="flex flex-col items-center gap-1.5">
            <p class="text-center text-xs text-muted-foreground">
                Infinito Reciclaje × EVOLVERE 2026 · Sistema de Gestión de Balanza v1
            </p>
            <x-powered-by-evolvere />
        </div>
    </footer>

    <x-slot:scripts>
        <script>
            (function () {
                const io = new IntersectionObserver(
                    function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('visible');
                                io.unobserve(entry.target);
                            }
                        });
                    },
                    { threshold: 0.08, rootMargin: '0px 0px -40px 0px' }
                );
                document.querySelectorAll('.reveal').forEach(function (el) {
                    io.observe(el);
                });
            })();
        </script>
    </x-slot:scripts>

</x-layouts.landing>
