<x-mail::message>
# Restablecer contraseña

Hola, {{ $user->name }}.

Se registró una solicitud de restablecimiento de contraseña para tu cuenta.

<x-mail::button :url="$url">
Restablecer contraseña
</x-mail::button>

El enlace expira en **{{ $expireMinutes }} minutos**.

Si no solicitaste el cambio, ignorá este email. Tu contraseña no se modifica.

Infinito Reciclaje

<x-mail::subcopy>
Si el botón no funciona, copiá y pegá esta URL en el navegador: {{ $url }}
</x-mail::subcopy>
</x-mail::message>
