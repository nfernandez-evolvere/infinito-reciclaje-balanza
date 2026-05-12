<x-layouts.showcase title="Input OTP — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Input OTP</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Input segmentado para códigos de verificación. Soporta paste, navegación con flechas y validación por patrón.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico — 6 dígitos</x-ui.typography>
        <x-ui.input-otp :length="6" name="otp" />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Solo números (pattern)</x-ui.typography>
        <x-ui.input-otp :length="6" name="pin" pattern="[0-9]" />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">4 dígitos</x-ui.typography>
        <x-ui.input-otp :length="4" name="code4" pattern="[0-9]" />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con valor inicial</x-ui.typography>
        <x-ui.input-otp :length="6" value="123456" name="prefilled" />
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Estado error</x-ui.typography>
        <x-ui.input-otp :length="6" state="destructive" />
        <x-ui.typography as="p" class="text-destructive">Código incorrecto. Intentá de nuevo.</x-ui.typography>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">En un formulario</x-ui.typography>
        <div class="max-w-sm space-y-4">
            <x-ui.form-field message="Ingresá el código que enviamos a tu email.">
                <x-ui.label>Código de verificación</x-ui.label>
                <x-ui.input-otp :length="6" name="verify_code" pattern="[0-9]" />
            </x-ui.form-field>
            <x-ui.button class="w-full">Verificar</x-ui.button>
        </div>
    </section>

</div>
</x-layouts.showcase>
