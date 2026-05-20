<x-mail::message>
# Verificación de correo electrónico

Hola, {{ $user->name }}.

Se creó una cuenta en el sistema de gestión de balanza con esta dirección de correo. Para activarla, verificá tu email.

<x-mail::button :url="$url">
Verificar correo
</x-mail::button>

El enlace expira en **60 minutos**.

Si no creaste una cuenta, ignorá este email.

{{ $orgNombre }}

<x-mail::subcopy>
Si el botón no funciona, copiá y pegá esta URL en el navegador: {{ $url }}
</x-mail::subcopy>
</x-mail::message>
