<x-mail::message>
# Activá tu cuenta

Hola.

Fuiste añadido como administrador de **{{ $orgNombre }}** en el sistema de gestión de balanza.

Hacé clic en el botón para crear tu contraseña y acceder por primera vez.

<x-mail::button :url="$url">
Activar cuenta
</x-mail::button>

El enlace expira en **{{ $expireMinutes }} minutos**.

Si no esperabas este email, podés ignorarlo sin problema.

{{ $orgNombre }}

<x-mail::subcopy>
Si el botón no funciona, copiá y pegá esta URL en el navegador: {{ $url }}
</x-mail::subcopy>
</x-mail::message>
