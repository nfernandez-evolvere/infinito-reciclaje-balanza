@props([])

<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="md">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.usuarios.store') }}'
                : '{{ url('admin/usuarios') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo usuario' : 'Editar usuario'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-2 pb-2">

                <x-ui.form-field
                    for="name"
                    :state="$errors->has('name') ? 'destructive' : null"
                    :message="$errors->first('name')"
                >
                    <x-ui.label for="name">Nombre completo</x-ui.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        x-model="form.name"
                        placeholder="Ej: Roberto García"
                        :state="$errors->has('name') ? 'destructive' : null"
                        autofocus
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="email"
                    :state="$errors->has('email') ? 'destructive' : null"
                    :message="$errors->first('email')"
                >
                    <x-ui.label for="email">Correo electrónico</x-ui.label>
                    <x-ui.input
                        id="email"
                        name="email"
                        type="email"
                        x-model="form.email"
                        placeholder="Ej: roberto@municipio.gob.ar"
                        :state="$errors->has('email') ? 'destructive' : null"
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="role"
                    :state="$errors->has('role') ? 'destructive' : null"
                    :message="$errors->first('role')"
                >
                    <x-ui.label for="role">Rol</x-ui.label>
                    <x-ui.select
                        name="role"
                        x-effect="value = String(form.role ?? '')"
                        @select-change="form.role = $event.detail.value"
                    >
                        <x-ui.select.trigger id="role" :state="$errors->has('role') ? 'destructive' : null">
                            <x-ui.select.value placeholder="Seleccionar rol…" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="operador">Operador</x-ui.select.item>
                            <x-ui.select.item value="admin">Admin</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                <div x-show="modalMode === 'create'" x-cloak class="space-y-2">

                    {{-- Contraseña + checklist de requisitos --}}
                    <div x-data="{ showPw: false }" class="grid gap-1.5">
                        <x-ui.label for="password">Contraseña inicial</x-ui.label>

                        <x-ui.input-group :class="$errors->has('password') ? 'border-destructive-border ring-2 ring-destructive/20' : ''">
                            <x-ui.input-group.input
                                id="password"
                                name="password"
                                x-bind:type="showPw ? 'text' : 'password'"
                                x-model="form.password"
                                autocomplete="new-password"
                                :aria-invalid="$errors->has('password') ? 'true' : null"
                            />
                            <x-ui.input-group.button type="button" class="rounded-full h-8 w-8" @click="showPw = !showPw" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                <x-lucide-eye     x-show="!showPw"        class="size-4" />
                                <x-lucide-eye-off x-show="showPw" x-cloak class="size-4" />
                            </x-ui.input-group.button>
                        </x-ui.input-group>

                        <ul class="mt-0.5 space-y-1.5">
                            @php
                                $rules = [
                                    ['expr' => 'form.password.length >= 8',                                         'label' => 'Mínimo 8 caracteres'],
                                    ['expr' => '/[a-z]/.test(form.password) && /[A-Z]/.test(form.password)',        'label' => 'Mayúsculas y minúsculas'],
                                    ['expr' => '/[0-9]/.test(form.password)',                                       'label' => 'Al menos un número'],
                                    ['expr' => '/[^a-zA-Z0-9\s]/.test(form.password)',                             'label' => 'Al menos un símbolo'],
                                ];
                            @endphp

                            @foreach ($rules as $rule)
                                <li class="flex items-center gap-2 text-xs transition-colors"
                                    :class="{{ $rule['expr'] }} ? 'text-success' : 'text-muted-foreground'">
                                    <x-lucide-circle-check x-show="{{ $rule['expr'] }}"            class="size-3.5 shrink-0" />
                                    <x-lucide-circle       x-show="!({{ $rule['expr'] }})" x-cloak class="size-3.5 shrink-0" />
                                    {{ $rule['label'] }}
                                </li>
                            @endforeach

                            @foreach ($errors->get('password') as $msg)
                                @if (str_contains($msg, 'filtración'))
                                    <li class="flex items-center gap-2 text-xs text-destructive">
                                        <x-lucide-circle-x class="size-3.5 shrink-0" />
                                        {{ $msg }}
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>

                    {{-- Confirmación de contraseña --}}
                    <x-ui.form-field
                        for="password_confirmation"
                        :state="$errors->has('password') ? 'destructive' : null"
                    >
                        <x-ui.label for="password_confirmation">Confirmar contraseña</x-ui.label>
                        <x-ui.input-group x-data="{ showPwConf: false }">
                            <x-ui.input-group.input
                                id="password_confirmation"
                                name="password_confirmation"
                                x-bind:type="showPwConf ? 'text' : 'password'"
                                x-model="form.password_confirmation"
                                autocomplete="new-password"
                                :aria-invalid="$errors->has('password') ? 'true' : null"
                            />
                            <x-ui.input-group.button type="button" class="rounded-full h-8 w-8" @click="showPwConf = !showPwConf" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                <x-lucide-eye     x-show="!showPwConf"        class="size-4" />
                                <x-lucide-eye-off x-show="showPwConf" x-cloak class="size-4" />
                            </x-ui.input-group.button>
                        </x-ui.input-group>
                    </x-ui.form-field>

                </div>

            </div>

            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit">
                    <x-lucide-save class="size-4" />
                    <span x-text="modalMode === 'create' ? 'Crear' : 'Guardar cambios'"></span>
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
