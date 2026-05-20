<x-mail::message>
# Acceso al sistema

Hola, {{ $user->name }}.

Tu cuenta en el sistema de gestión de {{ $organizacion?->nombre ?? config('app.name') }} está activa.

<x-mail::panel>
**Datos de acceso**

Correo: {{ $user->email }}
@if ($temporaryPassword)
Contraseña temporal: {{ $temporaryPassword }}
@endif
Rol: {{ $user->role === 'admin' ? 'Administrador' : 'Operador' }}
</x-mail::panel>

@if ($temporaryPassword)
Cambiá la contraseña desde la sección de perfil en el primer ingreso.
@endif

<x-mail::button :url="url(route('login'))">
Ingresar al sistema
</x-mail::button>

Si tenés consultas sobre el acceso, respondé este correo.

{{ $organizacion?->nombre ?? config('app.name') }}
</x-mail::message>
