@props([
    'value',        // valor de _intencion_tara que representa esta opción
    'titulo',       // título de la opción
    'descripcion',  // texto explicativo
])

{{--
    Tarjeta de radio para la decisión de corrección de tara.
    Vive dentro del x-data del modal: enlaza directo a form._intencion_tara.
    Tematizada en warning para coincidir con el <x-ui.alert state="warning"> que la contiene.
--}}
<x-ui.radio-card
    model="form._intencion_tara"
    name="_intencion_tara"
    :value="$value"
    state="warning"
    :title="$titulo"
    :description="$descripcion"
/>
