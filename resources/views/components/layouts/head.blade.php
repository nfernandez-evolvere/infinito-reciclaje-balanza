@props(['title'])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- Config pública de Reverb para que Echo conecte en runtime (sin hornearla en
     el build). Host/puerto/esquema son opcionales: si no están, el front cae al
     origen de la página (producción detrás del edge). --}}
@if($key = config('broadcasting.connections.reverb.key'))
<meta name="reverb-key" content="{{ $key }}">
@endif
@if($rh = config('reverb.client.host'))<meta name="reverb-host" content="{{ $rh }}">@endif
@if($rp = config('reverb.client.port'))<meta name="reverb-port" content="{{ $rp }}">@endif
@if($rs = config('reverb.client.scheme'))<meta name="reverb-scheme" content="{{ $rs }}">@endif
<title>{{ $title }} — {{ config('app.name') }}</title>
<link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="150x150">
<script>
    (function () {
        const theme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (!theme && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
        // Sidebar: leer cookie antes de que Alpine inicialice para evitar el flash
        const collapsed = (document.cookie.match(/sidebar_collapsed=([^;]+)/) || [, 'false'])[1] !== 'false';
        if (collapsed) document.documentElement.setAttribute('data-sidebar-collapsed', '');
    })();
</script>
<style>
    /* Estado inicial del sidebar antes de que Alpine inicialice — evita el flash */
    @media (min-width: 1024px) {
        [data-sidebar]:not([data-initialized]) { width: 16rem; }
        [data-sidebar-collapsed] [data-sidebar]:not([data-initialized]) { width: 3rem; }
    }
    @media (max-width: 1023px) {
        [data-sidebar]:not([data-initialized]) { transform: translateX(-100%); }
    }
</style>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
