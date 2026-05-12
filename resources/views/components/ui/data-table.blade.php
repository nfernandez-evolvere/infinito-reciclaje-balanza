@props([
    'columns'           => [],   // [['key','label','sortable'=>false,'hideable'=>true,'searchable'=>true,'class'=>'']]
    'data'              => [],   // array of associative arrays
    'caption'           => null,
    'searchable'        => true,
    'searchPlaceholder' => 'Buscar...',
    'paginate'          => true,
    'perPage'           => 10,
    'perPageOptions'    => [10, 25, 50, 100],
    'selectable'        => false,
    'emptyMessage'      => 'No hay resultados.',
])

{{--
    Componentes Blade reutilizados:
    ✅  <x-ui.input>           → búsqueda (estático, server-side)
    ✅  <x-ui.button>          → toolbar + paginación (estático)
    ✅  <x-ui.table>           → wrapper overflow + <table>
    ✅  <x-ui.table.header>    → <thead>
    ✅  <x-ui.table.row>       → <tr> del header (estático)
    ✅  <x-ui.table.body>      → <tbody>
    ❌  <x-ui.table.head/row/cell> dentro de x-for → imposible (Blade es server-side, x-for es client-side)
    ❌  <x-ui.checkbox> en filas dinámicas          → ídem
--}}

<div
    {{ $attributes->twMerge('space-y-4') }}
    x-data="{
        allRows:     @js($data).map((r, i) => ({ ...r, _idx: i })),
        columns:     @js($columns),
        search:      '',
        sortKey:     null,
        sortDir:     'asc',
        page:        1,
        perPage:     {{ (int) $perPage }},
        selected:    [],
        hiddenCols:  [],
        visDropOpen: false,

        get visibleColumns() {
            return this.columns.filter(c => !this.hiddenCols.includes(c.key));
        },
        get searchableKeys() {
            return this.columns.filter(c => c.searchable !== false).map(c => c.key);
        },
        get filteredRows() {
            const q = this.search.trim().toLowerCase();
            if (!q) return this.allRows;
            return this.allRows.filter(row =>
                this.searchableKeys.some(k => String(row[k] ?? '').toLowerCase().includes(q))
            );
        },
        get sortedRows() {
            if (!this.sortKey) return this.filteredRows;
            const key = this.sortKey, dir = this.sortDir;
            return [...this.filteredRows].sort((a, b) => {
                const cmp = String(a[key] ?? '').localeCompare(String(b[key] ?? ''), undefined, { numeric: true, sensitivity: 'base' });
                return dir === 'asc' ? cmp : -cmp;
            });
        },
        get totalPages() {
            return Math.max(1, Math.ceil(this.sortedRows.length / this.perPage));
        },
        get paginatedRows() {
            const s = (this.page - 1) * this.perPage;
            return this.sortedRows.slice(s, s + this.perPage);
        },
        get pageNumbers() {
            const t = this.totalPages, p = this.page, nums = [];
            if (t <= 7) { for (let i = 1; i <= t; i++) nums.push(i); return nums; }
            nums.push(1);
            if (p > 3) nums.push('…');
            for (let i = Math.max(2, p - 1); i <= Math.min(t - 1, p + 1); i++) nums.push(i);
            if (p < t - 2) nums.push('…');
            nums.push(t);
            return nums;
        },
        get allPageSelected() {
            return this.paginatedRows.length > 0 &&
                   this.paginatedRows.every(r => this.selected.includes(r._idx));
        },
        get somePageSelected() {
            return !this.allPageSelected && this.paginatedRows.some(r => this.selected.includes(r._idx));
        },
        toggleSort(key) {
            if (!this.columns.find(c => c.key === key)?.sortable) return;
            this.sortKey === key
                ? (this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc')
                : (this.sortKey = key, this.sortDir = 'asc');
            this.page = 1;
        },
        toggleSelect(idx) {
            const i = this.selected.indexOf(idx);
            i >= 0 ? this.selected.splice(i, 1) : this.selected.push(idx);
        },
        toggleSelectAll() {
            if (this.allPageSelected) {
                const ids = this.paginatedRows.map(r => r._idx);
                this.selected = this.selected.filter(i => !ids.includes(i));
            } else {
                this.paginatedRows.forEach(r => { if (!this.selected.includes(r._idx)) this.selected.push(r._idx); });
            }
        },
        toggleCol(key) {
            const i = this.hiddenCols.indexOf(key);
            i >= 0 ? this.hiddenCols.splice(i, 1) : this.hiddenCols.push(key);
        },
        setSearch(val) { this.search = val; this.page = 1; },
        setPerPage(n)  { this.perPage = Number(n); this.page = 1; },
    }"
    @click.outside="visDropOpen = false"
>

    {{-- ── Toolbar ─────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2">

        @if($searchable)
        <div class="flex-1 max-w-sm">
            <x-ui.input
                type="search"
                placeholder="{{ $searchPlaceholder }}"
                x-bind:value="search"
                @input="setSearch($event.target.value)"
            >
                <x-slot:leading>
                    <x-lucide-search class="size-full" />
                </x-slot:leading>
            </x-ui.input>
        </div>
        @endif

        {{-- Column visibility — reutiliza x-ui.button --}}
        <div class="relative ml-auto">
            <x-ui.button variant="outline" size="sm" @click="visDropOpen = !visDropOpen">
                <x-lucide-columns class="size-4" />
                Columnas
            </x-ui.button>

            <div
                x-show="visDropOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full z-50 mt-1 w-48 rounded-md border border-border bg-popover p-1 shadow-md"
            >
                <x-ui.typography as="muted" class="px-2 py-1.5 text-xs font-semibold">Alternar columnas</x-ui.typography>
                <template x-for="col in columns.filter(c => c.hideable !== false)" :key="col.key">
                    <button
                        type="button"
                        @click="toggleCol(col.key)"
                        class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground cursor-pointer"
                    >
                        {{-- Mini checkbox — no puede reusarse x-ui.checkbox porque el estado lo controla el data-table --}}
                        <span
                            class="flex size-4 shrink-0 items-center justify-center rounded border transition-colors"
                            :class="!hiddenCols.includes(col.key) ? 'bg-primary border-primary text-primary-foreground' : 'bg-background border-input'"
                        >
                            <x-lucide-check x-show="!hiddenCols.includes(col.key)" class="size-3" stroke-width="3" />
                        </span>
                        <span x-text="col.label" class="capitalize"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- ── Table — reutiliza x-ui.table, x-ui.table.header, x-ui.table.body ── --}}
    <x-ui.table>
        @if($caption)
            <x-ui.table.caption>{{ $caption }}</x-ui.table.caption>
        @endif

        {{-- thead — estático: se puede usar x-ui.table.header + x-ui.table.row --}}
        <x-ui.table.header>
            <x-ui.table.row class="hover:bg-transparent">

                @if($selectable)
                {{-- Select-all: estado controlado por data-table → no se puede rehusar x-ui.checkbox --}}
                <x-ui.table.head class="w-12">
                    <button
                        type="button"
                        role="checkbox"
                        :aria-checked="allPageSelected ? 'true' : (somePageSelected ? 'mixed' : 'false')"
                        @click="toggleSelectAll()"
                        :class="allPageSelected || somePageSelected
                            ? 'bg-primary border-primary text-primary-foreground'
                            : 'bg-background border-input'"
                        class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors cursor-pointer"
                    >
                        <x-lucide-check x-show="allPageSelected"                        class="size-3" stroke-width="3" />
                        <x-lucide-minus x-show="somePageSelected && !allPageSelected" class="size-3" stroke-width="3" />
                    </button>
                </x-ui.table.head>
                @endif

                {{-- Columnas dinámicas: dentro de x-for → no se puede usar x-ui.table.head --}}
                <template x-for="col in visibleColumns" :key="col.key">
                    <th
                        :class="[col.class ?? '', col.sortable ? 'cursor-pointer select-none' : '']"
                        class="h-10 px-3 text-left align-middle font-medium text-muted-foreground"
                        @click="toggleSort(col.key)"
                    >
                        <div class="flex items-center gap-1.5">
                            <span x-text="col.label"></span>
                            <template x-if="col.sortable">
                                <span class="text-muted-foreground/50" aria-hidden="true">
                                    <x-lucide-chevrons-up-down x-show="sortKey !== col.key"                        class="size-3.5" />
                                    <x-lucide-chevron-up x-show="sortKey === col.key && sortDir === 'asc'"  class="size-3.5 text-foreground" />
                                    <x-lucide-chevron-down x-show="sortKey === col.key && sortDir === 'desc'" class="size-3.5 text-foreground" />
                                </span>
                            </template>
                        </div>
                    </th>
                </template>
            </x-ui.table.row>
        </x-ui.table.header>

        {{-- tbody — filas dinámicas: dentro de x-for → no se puede usar x-ui.table.row/cell --}}
        <x-ui.table.body>
            <template x-if="paginatedRows.length > 0">
                <template x-for="row in paginatedRows" :key="row._idx">
                    <tr
                        class="border-b border-border transition-colors hover:bg-muted/50"
                        :class="selected.includes(row._idx) ? 'bg-muted' : ''"
                    >
                        @if($selectable)
                        <x-ui.table.cell class="w-12">
                            <button
                                type="button"
                                role="checkbox"
                                :aria-checked="selected.includes(row._idx).toString()"
                                @click="toggleSelect(row._idx)"
                                :class="selected.includes(row._idx)
                                    ? 'bg-primary border-primary text-primary-foreground'
                                    : 'bg-background border-input'"
                                class="size-4 shrink-0 rounded border flex items-center justify-center transition-colors cursor-pointer"
                            >
                                <x-lucide-check x-show="selected.includes(row._idx)" class="size-3" stroke-width="3" />
                            </button>
                        </x-ui.table.cell>
                        @endif

                        <template x-for="col in visibleColumns" :key="col.key">
                            <td :class="col.class ?? ''" class="p-3 align-middle" x-text="row[col.key] ?? '—'"></td>
                        </template>
                    </tr>
                </template>
            </template>

            <template x-if="paginatedRows.length === 0">
                <tr>
                    <td
                        :colspan="{{ $selectable ? 'visibleColumns.length + 1' : 'visibleColumns.length' }}"
                        class="h-24 text-center text-sm text-muted-foreground p-3"
                    >
                        {{ $emptyMessage }}
                    </td>
                </tr>
            </template>
        </x-ui.table.body>
    </x-ui.table>

    {{-- ── Footer ─────────────────────────────────────────────── --}}
    @if($paginate)
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-sm text-muted-foreground">

        {{-- Resultados + per-page --}}
        <div class="flex items-center gap-4">
            @if($selectable)
                <span x-text="`${selected.length} de ${sortedRows.length} fila(s) seleccionada(s).`"></span>
            @else
                <span x-text="`${sortedRows.length} resultado(s).`"></span>
            @endif

            <div class="flex items-center gap-1.5">
                <span>Filas:</span>
                <x-ui.select
                    value="{{ $perPage }}"
                    @select-change.stop="setPerPage($event.detail.value)"
                    class="w-20"
                >
                    <x-ui.select.trigger size="sm">
                        <x-ui.select.value />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        @foreach($perPageOptions as $opt)
                            <x-ui.select.item value="{{ $opt }}">{{ $opt }}</x-ui.select.item>
                        @endforeach
                    </x-ui.select.content>
                </x-ui.select>
            </div>
        </div>

        {{-- Paginación — reutiliza x-ui.button --}}
        <div class="flex items-center gap-1">
            <span x-text="`Página ${page} de ${totalPages}`" class="mr-2"></span>

            <x-ui.button
                variant="outline"
                size="icon"
                @click="page > 1 && page--"
                x-bind:disabled="page <= 1"
                class="size-8"
                aria-label="Página anterior"
            >
                <x-lucide-chevron-left class="size-4" />
            </x-ui.button>

            <template x-for="(num, i) in pageNumbers" :key="i">
                <span x-show="num === '…'" class="flex size-8 items-center justify-center text-muted-foreground" aria-hidden="true">…</span>
                <button
                    x-show="num !== '…'"
                    type="button"
                    @click="page = num"
                    :class="page === num
                        ? 'border border-input bg-background text-foreground shadow-sm'
                        : 'border border-transparent hover:bg-accent hover:text-accent-foreground'"
                    class="inline-flex size-8 items-center justify-center rounded-md text-sm transition-colors cursor-pointer"
                    x-text="num"
                ></button>
            </template>

            <x-ui.button
                variant="outline"
                size="icon"
                @click="page < totalPages && page++"
                x-bind:disabled="page >= totalPages"
                class="size-8"
                aria-label="Página siguiente"
            >
                <x-lucide-chevron-right class="size-4" />
            </x-ui.button>
        </div>
    </div>
    @endif
</div>
