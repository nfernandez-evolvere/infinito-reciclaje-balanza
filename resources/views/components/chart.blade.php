@props([
    'type'        => 'bar',
    'series'      => [],
    'categories'  => [],
    'height'      => 280,
    'colors'      => null,
    'stacked'     => false,
    'sparkline'   => false,
    'curve'       => 'smooth',
    'yformat'     => 'number',
    'horizontal'  => false,
    'dataLabels'  => false,
    'distributed' => false,
    'zoom'        => false,
    'strokeWidth' => null,
])

<div
    x-data="apexChart({{ Js::from([
        'type'        => $type,
        'series'      => $series,
        'categories'  => $categories,
        'height'      => $height,
        'colors'      => $colors,
        'stacked'     => $stacked,
        'sparkline'   => $sparkline,
        'curve'       => $curve,
        'yformat'     => $yformat,
        'horizontal'  => $horizontal,
        'dataLabels'  => $dataLabels,
        'distributed' => $distributed,
        'zoom'        => $zoom,
        'strokeWidth' => $strokeWidth,
    ]) }})"
    x-init="init()"
    {{ $attributes }}
>
    <div x-ref="chart"></div>
</div>
