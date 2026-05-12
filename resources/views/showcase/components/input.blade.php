<x-layouts.showcase title="Input — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Input</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Campo de texto de línea única. Soporta tamaños sm/md/lg, estados semánticos e íconos leading/trailing via named slots.</x-ui.typography>
    </div>

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="space-y-3">
            <x-ui.form-field>
                <x-ui.label>Small (sm)</x-ui.label>
                <x-ui.input size="sm" placeholder="Texto en sm..." />
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Medium (md) — default</x-ui.label>
                <x-ui.input placeholder="Texto en md..." />
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Large (lg)</x-ui.label>
                <x-ui.input size="lg" placeholder="Texto en lg..." />
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Tipos nativos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tipos HTML</x-ui.typography>
        <div class="space-y-3">
            <x-ui.input type="text"     placeholder="type=text" />
            <x-ui.input type="email"    placeholder="type=email" />
            <x-ui.input type="password" placeholder="type=password" />
            <x-ui.input type="number"   placeholder="type=number" />
            <x-ui.input type="search"   placeholder="type=search" />
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="space-y-4">
            <x-ui.form-field state="destructive" message="Este campo es requerido.">
                <x-ui.label state="destructive">Email</x-ui.label>
                <x-ui.input type="email" state="destructive" value="correo-invalido" />
            </x-ui.form-field>

            <x-ui.form-field state="success" message="Email disponible.">
                <x-ui.label state="success">Email</x-ui.label>
                <x-ui.input type="email" state="success" value="usuario@ejemplo.com" />
            </x-ui.form-field>

            <x-ui.form-field state="warning" message="Este email ya está registrado.">
                <x-ui.label state="warning">Email</x-ui.label>
                <x-ui.input type="email" state="warning" value="otro@ejemplo.com" />
            </x-ui.form-field>

            <x-ui.form-field state="info" message="Usaremos este email para enviarte notificaciones.">
                <x-ui.label state="info">Email</x-ui.label>
                <x-ui.input type="email" state="info" placeholder="nombre@ejemplo.com" />
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Disabled --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Deshabilitado</x-ui.typography>
        <x-ui.form-field>
            <x-ui.label>Campo deshabilitado</x-ui.label>
            <x-ui.input disabled value="No editable" />
        </x-ui.form-field>
    </section>

    <x-ui.separator />

    {{-- Con íconos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con íconos</x-ui.typography>
        <div class="space-y-3 max-w-sm">

            {{-- Leading: búsqueda --}}
            <x-ui.form-field>
                <x-ui.label>Búsqueda (leading)</x-ui.label>
                <x-ui.input placeholder="Buscar...">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </x-slot:leading>
                </x-ui.input>
            </x-ui.form-field>

            {{-- Trailing: email --}}
            <x-ui.form-field>
                <x-ui.label>Email (trailing)</x-ui.label>
                <x-ui.input type="email" placeholder="nombre@ejemplo.com">
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

            {{-- Ambos: URL --}}
            <x-ui.form-field>
                <x-ui.label>URL (leading + trailing)</x-ui.label>
                <x-ui.input placeholder="mi-sitio.com" value="laravel.com">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 1 1.242 7.244"/>
                        </svg>
                    </x-slot:leading>
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 7.5V6.108c0-1.135.845-2.098 1.976-2.192.373-.03.748-.057 1.123-.08M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08M15.75 18.75v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5A3.375 3.375 0 0 0 6.375 7.5H5.25m11.9-3.664A2.251 2.251 0 0 0 15 2.25h-1.5a2.251 2.251 0 0 0-2.15 1.586"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

            {{-- Password con toggle --}}
            <x-ui.form-field>
                <x-ui.label>Password (trailing interactivo)</x-ui.label>
                <div x-data="{ show: false }">
                    <x-ui.input
                        x-bind:type="show ? 'text' : 'password'"
                        placeholder="Contraseña"
                    >
                        <x-slot:leading>
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                            </svg>
                        </x-slot:leading>
                        <x-slot:trailing>
                            <button
                                type="button"
                                @click="show = !show"
                                class="pointer-events-auto text-muted-foreground hover:text-foreground transition-colors"
                                :aria-label="show ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                            >
                                <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                                <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-cloak>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </x-slot:trailing>
                    </x-ui.input>
                </div>
            </x-ui.form-field>

        </div>
    </section>

    <x-ui.separator />

    {{-- Íconos con tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Íconos en todos los tamaños</x-ui.typography>
        <div class="space-y-3 max-w-sm">
            <x-ui.input size="sm" placeholder="Buscar en sm...">
                <x-slot:leading>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </x-slot:leading>
            </x-ui.input>
            <x-ui.input placeholder="Buscar en md...">
                <x-slot:leading>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </x-slot:leading>
            </x-ui.input>
            <x-ui.input size="lg" placeholder="Buscar en lg...">
                <x-slot:leading>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                </x-slot:leading>
            </x-ui.input>
        </div>
    </section>

    <x-ui.separator />

    {{-- Íconos + estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Íconos con estados semánticos</x-ui.typography>
        <x-ui.typography as="muted">El ícono adopta el color del estado automáticamente.</x-ui.typography>

        @php
        $mailIcon = '<svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>';
        @endphp

        <div class="space-y-4 max-w-sm">

            <x-ui.form-field state="destructive" message="Este email ya está en uso.">
                <x-ui.label state="destructive">Email</x-ui.label>
                <x-ui.input type="email" state="destructive" value="ocupado@ejemplo.com">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </x-slot:leading>
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field state="success" message="Email disponible.">
                <x-ui.label state="success">Email</x-ui.label>
                <x-ui.input type="email" state="success" value="libre@ejemplo.com">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </x-slot:leading>
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field state="warning" message="Revisá que el email sea correcto.">
                <x-ui.label state="warning">Email</x-ui.label>
                <x-ui.input type="email" state="warning" value="raro@">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </x-slot:leading>
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field state="info" message="Usaremos este email para enviarte notificaciones.">
                <x-ui.label state="info">Email</x-ui.label>
                <x-ui.input type="email" state="info" placeholder="nombre@ejemplo.com">
                    <x-slot:leading>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                        </svg>
                    </x-slot:leading>
                    <x-slot:trailing>
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                        </svg>
                    </x-slot:trailing>
                </x-ui.input>
            </x-ui.form-field>

        </div>
    </section>

</div>
</x-layouts.showcase>
