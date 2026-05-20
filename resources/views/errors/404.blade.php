@if($showAppLayout ?? false)
    <x-layouts.app title="Página no encontrada">
        @include('errors._404-content', ['home' => $home])
    </x-layouts.app>
@else
    <x-layouts.auth title="Página no encontrada" :bare="true">
        @include('errors._404-content', ['home' => $home ?? route('login')])
    </x-layouts.auth>
@endif
