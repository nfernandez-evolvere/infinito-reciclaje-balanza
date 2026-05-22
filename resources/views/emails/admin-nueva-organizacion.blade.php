<x-mail::message>
# Nueva organización asignada

Hola, {{ $user->name }}.

Tu cuenta fue asignada como administrador de **{{ $organizacion->nombre }}**.

Podés acceder al sistema con tu usuario y contraseña habituales.

<x-mail::button :url="url(route('login'))">
Ingresar al sistema
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
