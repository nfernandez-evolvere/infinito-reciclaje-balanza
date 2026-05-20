<x-mail::message>
# Resumen de pesajes — {{ $periodo }}

<x-mail::panel>
Total acumulado: **{{ number_format($pesoTotalKg, 2, ',', '.') }} kg** · {{ number_format($pesajes->count(), 0, ',', '.') }} {{ $pesajes->count() === 1 ? 'pesaje' : 'pesajes' }}
</x-mail::panel>

<x-mail::table>
| Ticket | Material | Peso bruto | Tara | Peso neto | Operador |
|:-------|:---------|:----------:|:----:|:---------:|:---------|
@foreach ($pesajes as $pesaje)
| {{ $pesaje->ticket }} | {{ $pesaje->material }} | {{ number_format($pesaje->peso_bruto, 2, ',', '.') }} kg | {{ number_format($pesaje->tara, 2, ',', '.') }} kg | {{ number_format($pesaje->peso_neto, 2, ',', '.') }} kg | {{ $pesaje->operador->name ?? '—' }} |
@endforeach
</x-mail::table>

<x-mail::button :url="url(route('admin.pesajes.index'))">
Ver en el sistema
</x-mail::button>

{{ $organizacion?->nombre ?? config('app.name') }}
</x-mail::message>
