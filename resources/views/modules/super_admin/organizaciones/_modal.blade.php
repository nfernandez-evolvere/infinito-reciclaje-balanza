<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="lg">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('super.organizaciones.store') }}'
                : '{{ url('organizaciones') }}/' + form.id"
            class="flex flex-col min-h-0"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nueva organización' : 'Editar organización'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            {{-- Cuerpo: sin overflow para que los dropdowns no queden recortados --}}
            <div class="px-6 space-y-4 pb-2">

                {{-- Nombre --}}
                <div class="space-y-3 border rounded-md border-primary/20 bg-primary/2 p-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-primary">Organización</p>
                    <x-ui.form-field
                        for="nombre"
                        :state="$errors->has('nombre') ? 'destructive' : null"
                        :message="$errors->first('nombre')"
                    >
                        <x-ui.label for="nombre">Nombre</x-ui.label>
                        <x-ui.input
                            id="nombre"
                            name="nombre"
                            x-model="form.nombre"
                            placeholder="Ej: Municipalidad de Corrientes"
                            :state="$errors->has('nombre') ? 'destructive' : null"
                            autofocus
                        />
                    </x-ui.form-field>
                </div>

                {{-- ── CREAR: admin inicial ─────────────────────────────── --}}
                <div x-show="modalMode === 'create'" x-cloak>
                    {{-- sin overflow-hidden para que el dropdown no quede recortado --}}
                    <div class="border rounded-md border-primary/20 bg-primary/2">
                        <div class="flex items-center gap-2 px-4 py-3 border-b border-primary/15 bg-primary/5 rounded-t-md">
                            <x-lucide-user-circle class="size-4 text-primary" />
                            <p class="text-xs font-semibold uppercase tracking-widest text-primary">Usuario administrador</p>
                        </div>

                        <div class="p-4 space-y-3">
                            <input type="hidden" name="admin_email" x-bind:value="form.admin_email" />
                            <input type="hidden" name="admin_name"  x-bind:value="adminName" />

                            {{-- Combobox --}}
                            <div x-show="!selectedUser" class="space-y-1.5">
                                <x-ui.label>Buscar usuario o ingresar email</x-ui.label>
                                <div class="relative" @click.outside="userSearchOpen = false">
                                    <x-ui.input
                                        type="text"
                                        x-model="userQuery"
                                        x-on:input="debouncedUserSearch()"
                                        x-on:keydown.escape="userSearchOpen = false"
                                        placeholder="Nombre o email del administrador..."
                                        :state="$errors->has('admin_email') ? 'destructive' : null"
                                        autocomplete="off"
                                    >
                                        <x-slot:leading>
                                            <x-lucide-loader-circle x-show="userSearching" class="size-4 animate-spin text-primary" />
                                            <x-lucide-search x-show="!userSearching" class="size-4 text-primary" />
                                        </x-slot:leading>
                                    </x-ui.input>

                                    <div
                                        x-show="userSearchOpen"
                                        class="absolute top-full left-0 right-0 z-20 mt-1 rounded-md border border-border bg-popover text-popover-foreground shadow-lg overflow-hidden"
                                    >
                                        <template x-for="user in userResults" :key="user.id">
                                            <button
                                                type="button"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-primary/5 hover:text-foreground transition-colors cursor-pointer"
                                                x-on:click="selectUser(user)"
                                            >
                                                <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 text-[11px] font-semibold uppercase text-primary" x-text="user.name.charAt(0)"></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium truncate" x-text="user.name"></p>
                                                    <p class="text-xs text-muted-foreground truncate" x-text="user.email"></p>
                                                </div>
                                                <x-lucide-corner-down-left class="size-3.5 shrink-0 text-primary/50" />
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('admin_email')
                                    <p class="text-xs text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Usuario seleccionado --}}
                            <div x-show="selectedUser" class="flex items-center gap-3 px-3 py-2.5 rounded-md bg-success/5 border border-success/20">
                                <div class="size-8 rounded-full bg-success/15 flex items-center justify-center shrink-0 text-xs font-bold uppercase text-success" x-text="selectedUser?.name?.charAt(0) ?? '?'"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" x-text="selectedUser?.name"></p>
                                    <p class="text-xs text-muted-foreground truncate" x-text="selectedUser?.email"></p>
                                </div>
                                <button type="button" x-on:click="clearUser()" class="shrink-0 p-1 rounded text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
                                    <x-lucide-x class="size-4" />
                                </button>
                            </div>

                            {{-- Nombre del admin nuevo (solo cuando es email nuevo) --}}
                            <div x-show="!selectedUser && userQuery.includes('@')" x-cloak class="space-y-1.5">
                                <x-ui.label>Nombre completo</x-ui.label>
                                <x-ui.input
                                    type="text"
                                    x-model="adminName"
                                    placeholder="Ej: Roberto García"
                                    autocomplete="off"
                                />
                            </div>

                            {{-- Aviso: usuario nuevo --}}
                            <div x-show="!selectedUser && userQuery.includes('@')" class="flex items-start gap-2.5 rounded-md border border-primary/20 bg-primary/5 px-3 py-2.5">
                                <x-lucide-mail class="size-4 mt-0.5 shrink-0 text-primary" />
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Este email no existe en el sistema. Se creará la cuenta y se enviará un
                                    <strong class="font-medium text-foreground">email de invitación</strong>
                                    para que active su acceso y cree su contraseña.
                                </p>
                            </div>

                            {{-- Aviso: usuario existente --}}
                            <div x-show="selectedUser" class="flex items-start gap-2.5 rounded-md border border-primary/20 bg-primary/5 px-3 py-2.5">
                                <x-lucide-mail class="size-4 mt-0.5 shrink-0 text-primary" />
                                <p class="text-xs text-muted-foreground leading-relaxed">
                                    Se enviará un <strong class="font-medium text-foreground">email de notificación</strong>
                                    informando que fue asignado como administrador de esta organización.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── EDITAR: tabla de usuarios ────────────────────────── --}}
                <div x-show="modalMode === 'edit'" x-cloak>
                    <div class="border rounded-md border-primary/20">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-primary/15 bg-primary/5 rounded-t-md">
                            <div class="flex items-center gap-2">
                                <x-lucide-users class="size-4 text-primary" />
                                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Usuarios</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-primary/10 text-primary px-2 py-0.5 text-xs font-semibold" x-text="orgUsers.length"></span>
                        </div>

                        {{-- Lista con scroll interno --}}
                        <div class="divide-y divide-border max-h-60 overflow-y-auto">
                            <template x-if="orgUsers.length === 0">
                                <div class="px-4 py-6 text-center text-xs text-muted-foreground">
                                    No hay usuarios asignados a esta organización.
                                </div>
                            </template>

                            <template x-for="u in orgUsers" :key="u.id">
                                <div class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-primary/2 transition-colors">
                                    <div
                                        class="size-8 rounded-full bg-primary/10 flex items-center justify-center shrink-0 text-xs font-semibold uppercase text-primary"
                                        x-text="u.name.charAt(0)"
                                    ></div>
                                    <div x-show="pendingRemoveId !== u.id" class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate" x-text="u.name"></p>
                                        <p class="text-xs text-muted-foreground truncate" x-text="u.email"></p>
                                    </div>

                                    {{-- Acciones normales --}}
                                    <div x-show="pendingRemoveId !== u.id" class="flex items-center gap-1 shrink-0">
                                        <span
                                            class="shrink-0 hidden sm:inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium mr-1"
                                            x-bind:class="u.role === 'admin' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'"
                                            x-text="u.role === 'admin' ? 'Admin' : 'Operador'"
                                        ></span>
                                        <x-ui.button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            x-on:click="resetOrgUserPassword(u.id)"
                                            x-bind:disabled="addWorking"
                                        >
                                            <x-lucide-refresh-cw class="size-3.5" />
                                            <span class="hidden sm:inline">Reenviar link</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            state="destructive"
                                            x-on:click="confirmRemoveOrgUser(u.id)"
                                            x-bind:disabled="addWorking"
                                        >
                                            <x-lucide-user-minus class="size-3.5" />
                                            <span class="hidden sm:inline">Quitar</span>
                                        </x-ui.button>
                                    </div>

                                    {{-- Confirm quitar --}}
                                    <div x-show="pendingRemoveId === u.id" class="flex items-center gap-2 shrink-0">
                                        <span class="text-xs text-muted-foreground whitespace-nowrap">
                                            ¿Quitar a <strong class="font-medium text-foreground" x-text="u.name"></strong>?
                                        </span>
                                        <x-ui.button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            x-on:click="pendingRemoveId = null"
                                        >
                                            <x-lucide-x class="size-3.5" />
                                            <span class="hidden sm:inline">Cancelar</span>
                                        </x-ui.button>
                                        <x-ui.button
                                            type="button"
                                            size="sm"
                                            state="destructive"
                                            x-on:click="removeOrgUser(u.id)"
                                            x-bind:disabled="addWorking"
                                        >
                                            <x-lucide-user-minus class="size-3.5" />
                                            <span class="hidden sm:inline">Quitar</span>
                                        </x-ui.button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Agregar usuario: fuera del scroll, dropdown se extiende libremente --}}
                        <div class="border-t border-primary/15 px-4 py-3 bg-primary/3 space-y-2 rounded-b-md">
                            <div class="flex items-center gap-1.5">
                                <x-lucide-user-plus class="size-3.5 text-primary" />
                                <p class="text-xs font-semibold text-primary">Agregar usuario</p>
                            </div>
                            <div class="flex gap-2 items-center">
                                <div class="relative flex-1 min-w-0" @click.outside="addSearchOpen = false">
                                    <x-ui.input
                                        type="text"
                                        x-model="addQuery"
                                        x-on:input="debouncedAddSearch()"
                                        x-on:keydown.escape="addSearchOpen = false"
                                        x-on:keydown.enter.prevent="addQuery.includes('@') && !addSearchOpen ? addOrgUser(addQuery) : null"
                                        placeholder="Nombre o email..."
                                        autocomplete="off"
                                    >
                                        <x-slot:leading>
                                            <x-lucide-loader-circle x-show="addSearching || addWorking" class="size-4 animate-spin text-primary" />
                                            <x-lucide-search x-show="!addSearching && !addWorking" class="size-4 text-primary" />
                                        </x-slot:leading>
                                    </x-ui.input>

                                    <div
                                        x-show="addSearchOpen"
                                        class="absolute bottom-full left-0 right-0 z-20 mb-1 rounded-md border border-border bg-popover text-popover-foreground shadow-lg overflow-hidden"
                                    >
                                        <template x-for="user in addResults" :key="user.id">
                                            <button
                                                type="button"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-primary/5 hover:text-foreground transition-colors cursor-pointer"
                                                x-on:click="addOrgUser(user.email)"
                                            >
                                                <div class="size-7 rounded-full bg-primary/10 flex items-center justify-center shrink-0 text-[11px] font-semibold uppercase text-primary" x-text="user.name.charAt(0)"></div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium truncate" x-text="user.name"></p>
                                                    <p class="text-xs text-muted-foreground truncate" x-text="user.email"></p>
                                                </div>
                                                <x-lucide-plus class="size-3.5 shrink-0 text-primary" />
                                            </button>
                                        </template>

                                        <template x-if="addQuery.includes('@') && addResults.length === 0">
                                            <button
                                                type="button"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left text-sm hover:bg-primary/5 transition-colors cursor-pointer"
                                                x-on:click="addSearchOpen = false"
                                            >
                                                <div class="size-7 rounded-full bg-primary/15 flex items-center justify-center shrink-0">
                                                    <x-lucide-user-plus class="size-3.5 text-primary" />
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium text-sm truncate text-primary" x-text="'Invitar: ' + addQuery"></p>
                                                    <p class="text-xs text-muted-foreground">Completá el nombre y hacé clic en Agregar</p>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Nombre para usuario nuevo --}}
                            <div x-show="addQuery.includes('@') && addResults.length === 0 && !addSearchOpen && !addWorking" x-cloak class="space-y-1.5">
                                <x-ui.label>Nombre completo</x-ui.label>
                                <x-ui.input
                                    type="text"
                                    x-model="addNewName"
                                    placeholder="Ej: Roberto García"
                                    autocomplete="off"
                                />
                            </div>

                            <x-ui.button
                                type="button"
                                x-show="addQuery.includes('@') && addResults.length === 0 && !addSearchOpen && !addWorking"
                                x-cloak
                                size="sm"
                                x-on:click="addOrgUser(addQuery)"
                                x-bind:disabled="addWorking || !addNewName.trim()"
                            >
                                <x-lucide-plus class="size-3.5" />
                                Agregar
                            </x-ui.button>

                            <x-ui.alert state="destructive" x-show="addError" x-cloak>
                                <x-lucide-circle-alert class="size-4" />
                                <x-ui.alert.title>No se pudo realizar el cambio.</x-ui.alert.title>
                                <x-ui.alert.description x-text="addError"></x-ui.alert.description>
                            </x-ui.alert>
                        </div>

                    </div>
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
