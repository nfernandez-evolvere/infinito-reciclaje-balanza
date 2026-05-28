@props(['zonas', 'tiposServicio', 'tiposVehiculo'])

<x-ui.card>
    <x-ui.card.content class="pt-6">
        <form method="GET" action="{{ route('admin.reportes.index') }}"
              x-data="{
                  desde: '{{ request('desde', '') }}',
                  hasta: '{{ request('hasta', '') }}',
                  setMesActual() {
                      const now = new Date();
                      const y = now.getFullYear();
                      const m = String(now.getMonth() + 1).padStart(2, '0');
                      const d = String(now.getDate()).padStart(2, '0');
                      const lastDay = new Date(y, now.getMonth() + 1, 0).getDate();
                      this.desde = `${y}-${m}-01`;
                      this.hasta = `${y}-${m}-${String(lastDay).padStart(2, '0')}`;
                  },
                  setMesAnterior() {
                      const now = new Date();
                      const d = new Date(now.getFullYear(), now.getMonth(), 0);
                      const y = d.getFullYear();
                      const m = String(d.getMonth() + 1).padStart(2, '0');
                      const last = d.getDate();
                      this.desde = `${y}-${m}-01`;
                      this.hasta = `${y}-${m}-${last}`;
                  }
              }">

            <div class="flex flex-col gap-4">
                {{-- Fila 1: período --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex items-center gap-2">
                        <x-ui.button type="button" variant="outline" size="sm" @click="setMesActual()">
                            Mes actual
                        </x-ui.button>
                        <x-ui.button type="button" variant="outline" size="sm" @click="setMesAnterior()">
                            Mes anterior
                        </x-ui.button>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="space-y-1">
                            <x-ui.label for="desde" class="text-xs">Desde</x-ui.label>
                            <x-ui.input id="desde" name="desde" type="date"
                                        x-model="desde"
                                        :value="request('desde')"
                                        class="w-36" />
                        </div>
                        <div class="space-y-1">
                            <x-ui.label for="hasta" class="text-xs">Hasta</x-ui.label>
                            <x-ui.input id="hasta" name="hasta" type="date"
                                        x-model="hasta"
                                        :value="request('hasta')"
                                        class="w-36" />
                        </div>
                    </div>
                </div>

                {{-- Fila 2: filtros opcionales --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="space-y-1">
                        <x-ui.label for="zona_id" class="text-xs">Zona</x-ui.label>
                        <x-ui.select id="zona_id" name="zona_id" class="w-44">
                            <option value="">Todas las zonas</option>
                            @foreach($zonas as $zona)
                                <option value="{{ $zona->id }}" @selected(request('zona_id') == $zona->id)>
                                    {{ $zona->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="space-y-1">
                        <x-ui.label for="tipo_servicio_id" class="text-xs">Servicio</x-ui.label>
                        <x-ui.select id="tipo_servicio_id" name="tipo_servicio_id" class="w-44">
                            <option value="">Todos los servicios</option>
                            @foreach($tiposServicio as $ts)
                                <option value="{{ $ts->id }}" @selected(request('tipo_servicio_id') == $ts->id)>
                                    {{ $ts->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div class="space-y-1">
                        <x-ui.label for="tipo_vehiculo_id" class="text-xs">Vehículo</x-ui.label>
                        <x-ui.select id="tipo_vehiculo_id" name="tipo_vehiculo_id" class="w-44">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposVehiculo as $tv)
                                <option value="{{ $tv->id }}" @selected(request('tipo_vehiculo_id') == $tv->id)>
                                    {{ $tv->nombre }}
                                </option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <x-ui.button type="submit">
                        <x-lucide-bar-chart-2 class="size-4 mr-2" />
                        Generar reporte
                    </x-ui.button>
                </div>
            </div>
        </form>
    </x-ui.card.content>
</x-ui.card>
