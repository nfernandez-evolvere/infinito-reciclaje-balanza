<x-mail::message>
# Reporte pendiente de revisión

El reporte **{{ $nombreReporte }}** correspondiente a **{{ $periodo }}** ya se generó y está esperando tu aprobación. No se enviará a los destinatarios hasta que lo revises.

<x-mail::panel>
Reporte: **{{ $nombreReporte }}**
Período: **{{ $periodo }}**
</x-mail::panel>

<x-mail::button :url="$url">
Revisar reporte
</x-mail::button>

Desde el historial podés previsualizar el contenido, ajustar el análisis, aprobar el envío o descartarlo.

Infinito Reciclaje — Gestión Integral de Residuos

<x-mail::subcopy>
Este es un mensaje automático generado por el Sistema de Balanza.
</x-mail::subcopy>
</x-mail::message>
