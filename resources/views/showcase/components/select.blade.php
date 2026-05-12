<x-layouts.showcase title="Select — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Select</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Select accesible con dropdown customizado. API compositiva que refleja shadcn: Trigger, Value, Content, Group, Label, Item, Separator. Soporta teclado (↑↓ Enter Esc), grupos y opciones deshabilitadas.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.select class="w-[200px]">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Seleccioná..." />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.group>
                    <x-ui.select.item value="light">Light</x-ui.select.item>
                    <x-ui.select.item value="dark">Dark</x-ui.select.item>
                    <x-ui.select.item value="system">System</x-ui.select.item>
                </x-ui.select.group>
            </x-ui.select.content>
        </x-ui.select>
    </section>

    <x-ui.separator />

    {{-- Con valor inicial --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con valor seleccionado</x-ui.typography>
        <x-ui.select name="country" value="ar" class="w-[220px]">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Seleccioná un país..." />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.group>
                    <x-ui.select.item value="ar">Argentina</x-ui.select.item>
                    <x-ui.select.item value="br">Brasil</x-ui.select.item>
                    <x-ui.select.item value="cl">Chile</x-ui.select.item>
                    <x-ui.select.item value="uy">Uruguay</x-ui.select.item>
                    <x-ui.select.item value="py">Paraguay</x-ui.select.item>
                </x-ui.select.group>
            </x-ui.select.content>
        </x-ui.select>
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="space-y-3 max-w-xs">
            <x-ui.form-field>
                <x-ui.label>Small (sm)</x-ui.label>
                <x-ui.select>
                    <x-ui.select.trigger size="sm">
                        <x-ui.select.value placeholder="Seleccioná..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="ar">Argentina</x-ui.select.item>
                        <x-ui.select.item value="br">Brasil</x-ui.select.item>
                        <x-ui.select.item value="cl">Chile</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Medium (md) — default</x-ui.label>
                <x-ui.select>
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Seleccioná..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="ar">Argentina</x-ui.select.item>
                        <x-ui.select.item value="br">Brasil</x-ui.select.item>
                        <x-ui.select.item value="cl">Chile</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Large (lg)</x-ui.label>
                <x-ui.select>
                    <x-ui.select.trigger size="lg">
                        <x-ui.select.value placeholder="Seleccioná..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="ar">Argentina</x-ui.select.item>
                        <x-ui.select.item value="br">Brasil</x-ui.select.item>
                        <x-ui.select.item value="cl">Chile</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Grupos y separador --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Grupos con SelectLabel y SelectSeparator</x-ui.typography>
        <x-ui.select class="w-[240px]">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Elegí una fruta..." />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.group>
                    <x-ui.select.label>Frutas</x-ui.select.label>
                    <x-ui.select.item value="manzana">Manzana</x-ui.select.item>
                    <x-ui.select.item value="banana">Banana</x-ui.select.item>
                    <x-ui.select.item value="naranja">Naranja</x-ui.select.item>
                </x-ui.select.group>
                <x-ui.select.separator />
                <x-ui.select.group>
                    <x-ui.select.label>Verduras</x-ui.select.label>
                    <x-ui.select.item value="zanahoria">Zanahoria</x-ui.select.item>
                    <x-ui.select.item value="lechuga">Lechuga</x-ui.select.item>
                    <x-ui.select.item value="tomate">Tomate</x-ui.select.item>
                </x-ui.select.group>
            </x-ui.select.content>
        </x-ui.select>
    </section>

    <x-ui.separator />

    {{-- Opciones deshabilitadas --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Opciones deshabilitadas</x-ui.typography>
        <x-ui.select class="w-[240px]">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Elegí un plan..." />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="free">Free</x-ui.select.item>
                <x-ui.select.item value="pro">Pro</x-ui.select.item>
                <x-ui.select.item value="enterprise" disabled>Enterprise (próximamente)</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos en el trigger</x-ui.typography>
        <div class="space-y-4 max-w-xs">
            <x-ui.form-field state="destructive" message="Seleccioná un rol válido.">
                <x-ui.label state="destructive">Rol</x-ui.label>
                <x-ui.select>
                    <x-ui.select.trigger state="destructive">
                        <x-ui.select.value placeholder="Seleccioná un rol..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                        <x-ui.select.item value="editor">Editor</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
            <x-ui.form-field state="success" message="Rol disponible.">
                <x-ui.label state="success">Rol</x-ui.label>
                <x-ui.select value="editor">
                    <x-ui.select.trigger state="success">
                        <x-ui.select.value placeholder="Seleccioná..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="editor">Editor</x-ui.select.item>
                        <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
            <x-ui.form-field state="warning" message="Este rol tiene permisos elevados.">
                <x-ui.label state="warning">Rol</x-ui.label>
                <x-ui.select value="admin">
                    <x-ui.select.trigger state="warning">
                        <x-ui.select.value placeholder="Seleccioná..." />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                        <x-ui.select.item value="editor">Editor</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Deshabilitado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Trigger deshabilitado</x-ui.typography>
        <x-ui.select value="pro" class="w-[200px]">
            <x-ui.select.trigger disabled>
                <x-ui.select.value />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="pro">Pro</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </section>

    <x-ui.separator />

    {{-- En contexto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — formulario</x-ui.typography>
        <x-ui.card class="max-w-sm">
            <x-ui.card.header>
                <x-ui.card.title>Nuevo miembro</x-ui.card.title>
                <x-ui.card.description>Completá los datos para agregar al equipo.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content class="space-y-4">
                <x-ui.form-field>
                    <x-ui.label>Departamento</x-ui.label>
                    <x-ui.select name="department">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Seleccioná..." />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.group>
                                <x-ui.select.label>Tecnología</x-ui.select.label>
                                <x-ui.select.item value="eng">Ingeniería</x-ui.select.item>
                                <x-ui.select.item value="design">Diseño</x-ui.select.item>
                                <x-ui.select.item value="product">Producto</x-ui.select.item>
                            </x-ui.select.group>
                            <x-ui.select.separator />
                            <x-ui.select.group>
                                <x-ui.select.label>Negocio</x-ui.select.label>
                                <x-ui.select.item value="marketing">Marketing</x-ui.select.item>
                                <x-ui.select.item value="sales">Ventas</x-ui.select.item>
                                <x-ui.select.item value="hr">Recursos Humanos</x-ui.select.item>
                            </x-ui.select.group>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>
                <x-ui.form-field>
                    <x-ui.label>Rol</x-ui.label>
                    <x-ui.select name="role" value="member">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Seleccioná..." />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="owner">Owner</x-ui.select.item>
                            <x-ui.select.item value="admin">Admin</x-ui.select.item>
                            <x-ui.select.item value="member">Member</x-ui.select.item>
                            <x-ui.select.item value="viewer">Viewer</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>
            </x-ui.card.content>
            <x-ui.card.footer>
                <x-ui.button size="sm">Agregar miembro</x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
