@props([
    'type'    => 'line',    // line | bar | pie | doughnut | radar | polarArea
    'data'    => [],        // Chart.js data object { labels, datasets }
    'options' => [],        // Chart.js options (merged over defaults)
    'height'  => '300px',
    'aspectRatio' => null,  // null = usa height fijo; número = mantiene ratio
])

@php
$defaultOptions = [
    'responsive'          => true,
    'maintainAspectRatio' => $aspectRatio !== null,
    'aspectRatio'         => $aspectRatio,
    'plugins' => [
        'legend' => [
            'position' => 'bottom',
            'labels'   => ['padding' => 16, 'usePointStyle' => true],
        ],
    ],
];
@endphp

<div
    {{ $attributes->twMerge('relative w-full') }}
    @style(["height:{$height}" => $aspectRatio === null])
>
    <canvas
        x-data="{
            chart: null,
            fnify(obj) {
                if (typeof obj === 'string') {
                    if (obj.includes('var(--')) {
                        const el = document.createElement('div');
                        el.style.color = obj;
                        document.body.appendChild(el);
                        const resolved = getComputedStyle(el).color;
                        el.remove();
                        return resolved || obj;
                    }
                }
                if (obj !== null && typeof obj === 'object') {
                    for (const k in obj) obj[k] = this.fnify(obj[k]);
                }
                return obj;
            },
            init() {
                const ctx = this.$el.getContext('2d');
                const defaults = @js($defaultOptions);
                const overrides = this.fnify(@js($options));
                const opts = Alpine.mergeDeep ? Alpine.mergeDeep(defaults, overrides) : { ...defaults, ...overrides };
                this.chart = new Chart(ctx, {
                    type: @js($type),
                    data: @js($data),
                    options: opts,
                });
            },
            destroy() {
                if (this.chart) { this.chart.destroy(); this.chart = null; }
            }
        }"
        x-init="init()"
        @destroy="destroy()"
        class="w-full h-full"
    ></canvas>
</div>
