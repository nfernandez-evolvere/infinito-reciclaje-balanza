<x-mail::message>
# Reporte de Pesajes — {{ $periodo }}

Estimados,

Se adjunta el reporte de pesajes correspondiente a **{{ $periodo }}**, generado por **{{ $municipalidad }}**.

El documento incluye el resumen de actividad del período, evolución diaria, desglose por zona y por tipo de vehículo.

<x-mail::panel>
Período: **{{ $periodo }}**
{{ count($nombresAdjuntos) > 1 ? 'Adjuntos' : 'Adjunto' }}: {{ implode(', ', $nombresAdjuntos) }}
</x-mail::panel>

Infinito Reciclaje — Gestión Integral de Residuos

<x-mail::subcopy>
Este es un mensaje automático generado por el Sistema de Balanza.
</x-mail::subcopy>
</x-mail::message>
