@php
    $authenticated = false;
    $userRole = null;
    try {
        $authenticated = auth()->check();
        $userRole = auth()->user()?->role;
    } catch (\Throwable) {}
@endphp

@if($authenticated)
    <x-layouts.app title="Inconvenientes técnicos">
        @include('errors._500-content', ['userRole' => $userRole])
    </x-layouts.app>
@else
    <x-layouts.auth title="Inconvenientes técnicos">
        @include('errors._500-content', ['userRole' => $userRole])
    </x-layouts.auth>
@endif
