<x-mail::message>
# Reporte de Alertas — {{ $periodo }}

Estimados,

Se adjunta el reporte de pesajes con alertas de peso correspondiente a **{{ $periodo }}**, generado por **{{ $municipalidad }}**.

El documento incluye los **{{ $totalAlertas }} pesaje{{ $totalAlertas !== 1 ? 's' : '' }}** que registraron alerta de peso durante el período.

<x-mail::panel>
Período: **{{ $periodo }}**
Pesajes con alerta: **{{ $totalAlertas }}**
Adjunto: {{ $filename }}
</x-mail::panel>

Infinito Reciclaje — Gestión Integral de Residuos

<x-mail::subcopy>
Este es un mensaje automático generado por el Sistema de Balanza.
</x-mail::subcopy>
</x-mail::message>
