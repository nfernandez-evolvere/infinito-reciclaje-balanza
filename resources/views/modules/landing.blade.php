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
            }
        </style>
    </x-slot:head>

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-border bg-background/90 backdrop-blur-sm">
        <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-2.5">
                <div class="flex size-7 shrink-0 items-center justify-center rounded-md bg-primary">
                    <span class="text-[11px] font-bold leading-none text-primary-foreground">IR</span>
                </div>
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

        {{-- Hero --}}
        <section class="relative overflow-hidden py-20 md:py-28">

            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-40 left-1/2 size-160 -translate-x-1/2 rounded-full bg-primary/5"></div>
                <div class="absolute bottom-0 left-1/4 size-80 -translate-y-1/2 rounded-full bg-primary/5"></div>
                <div class="absolute right-0 top-1/2 size-64 -translate-y-1/2 translate-x-1/2 rounded-full bg-primary/5"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 sm:px-6">

                <div class="mx-auto max-w-3xl text-center">
                    <span class="text-overline reveal">Sistema de Gestión de Balanza</span>
                    <h1 class="mt-4 text-balance text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl reveal delay-100">
                        Control del reciclaje,<br class="hidden sm:block"> sin planillas.
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
                <div class="mt-14 overflow-hidden rounded-xl shadow-2xl ring-1 ring-foreground/10 reveal delay-500">
                    <img class="block w-full dark:hidden" src="/assets/dashboard-2-desktop-light.png" alt="Dashboard" />
                    <img class="hidden w-full dark:block" src="/assets/dashboard-2-desktop-dark.png" alt="Dashboard" />
                </div>

            </div>
        </section>

        {{-- Features --}}
        <section class="py-16 md:py-24">
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
                        <div class="overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5">
                            <img class="block w-full dark:hidden" src="/assets/pesajes-desktop-light.png" alt="Registro de pesaje" />
                            <img class="hidden w-full dark:block" src="/assets/pesajes-desktop-dark.png" alt="Registro de pesaje" />
                        </div>
                        <div class="absolute bottom-0 right-0 w-[22%] overflow-hidden rounded-xl shadow-2xl ring-2 ring-background">
                            <img class="block w-full dark:hidden" src="/assets/pesajes-mobile-light.png" alt="Pesaje mobile" />
                            <img class="hidden w-full dark:block" src="/assets/pesajes-mobile-dark.png" alt="Pesaje mobile" />
                        </div>
                    </div>

                </div>

                {{-- Feature 2: Historial --}}
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">

                    <div class="order-last lg:order-first overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5 reveal">
                        <img class="block w-full dark:hidden" src="/assets/historia-pesajes-light.png" alt="Historial de pesajes" />
                        <img class="hidden w-full dark:block" src="/assets/historia-pesajes-dark.png" alt="Historial de pesajes" />
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
                        <div class="overflow-hidden rounded-xl shadow-xl ring-1 ring-foreground/5">
                            <img class="block w-full dark:hidden" src="/assets/dashboard-desktop-light.png" alt="Dashboard" />
                            <img class="hidden w-full dark:block" src="/assets/dashboard-desktop-dark.png" alt="Dashboard" />
                        </div>
                        <div class="absolute bottom-0 right-0 w-[22%] overflow-hidden rounded-xl shadow-2xl ring-2 ring-background">
                            <img class="block w-full dark:hidden" src="/assets/dashboard-mobile-light.png" alt="Dashboard mobile" />
                            <img class="hidden w-full dark:block" src="/assets/dashboard-mobile-dark.png" alt="Dashboard mobile" />
                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- CTA final --}}
        <section class="bg-primary py-16">
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

    </main>

    {{-- Footer --}}
    <footer class="border-t border-border py-6">
        <p class="text-center text-xs text-muted-foreground">
            Infinito Reciclaje × EVOLVERE 2026 · Sistema de Gestión de Balanza v1
        </p>
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
