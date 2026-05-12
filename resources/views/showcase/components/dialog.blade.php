<x-layouts.showcase title="Dialog — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Dialog</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Modal centrado con overlay. Soporta botón de cierre customizable, footer sticky y contenido scrollable.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.dialog>
            <x-ui.dialog.trigger>
                <x-ui.button variant="outline">Edit Profile</x-ui.button>
            </x-ui.dialog.trigger>

            <x-ui.dialog.content>
                <x-ui.dialog.header>
                    <x-ui.dialog.title>Edit profile</x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Make changes to your profile here. Click save when you're done.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <div class="px-6 py-4 space-y-3">
                    <x-ui.form-field>
                        <x-ui.label>Name</x-ui.label>
                        <x-ui.input value="Pedro Duarte" />
                    </x-ui.form-field>
                    <x-ui.form-field>
                        <x-ui.label>Username</x-ui.label>
                        <x-ui.input value="@peduarte" />
                    </x-ui.form-field>
                </div>

                <x-ui.dialog.footer>
                    <x-ui.button>Save changes</x-ui.button>
                </x-ui.dialog.footer>
            </x-ui.dialog.content>
        </x-ui.dialog>
    </section>

    <x-ui.separator />

    {{-- Custom Close Button --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Custom Close Button</x-ui.typography>
        <x-ui.typography as="muted">Replace the default close control with your own button using <x-ui.typography as="code">&lt;x-ui.dialog.close&gt;</x-ui.typography>.</x-ui.typography>

        <x-ui.dialog>
            <x-ui.dialog.trigger>
                <x-ui.button variant="outline">Custom Close Button</x-ui.button>
            </x-ui.dialog.trigger>

            <x-ui.dialog.content :showCloseButton="false">
                <x-ui.dialog.header>
                    <x-ui.dialog.title>Are you absolutely sure?</x-ui.dialog.title>
                    <x-ui.dialog.description>
                        This action cannot be undone. This will permanently delete your account
                        and remove your data from our servers.
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <x-ui.dialog.footer>
                    <x-ui.dialog.close>
                        <x-ui.button variant="outline">Cancel</x-ui.button>
                    </x-ui.dialog.close>
                    <x-ui.button state="destructive">Delete account</x-ui.button>
                </x-ui.dialog.footer>
            </x-ui.dialog.content>
        </x-ui.dialog>
    </section>

    <x-ui.separator />

    {{-- No Close Button --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">No Close Button</x-ui.typography>
        <x-ui.typography as="muted">Use <x-ui.typography as="code">:showCloseButton="false"</x-ui.typography> to hide the close button. The user must choose an action.</x-ui.typography>

        <x-ui.dialog>
            <x-ui.dialog.trigger>
                <x-ui.button variant="outline">No Close Button</x-ui.button>
            </x-ui.dialog.trigger>

            <x-ui.dialog.content :showCloseButton="false">
                <x-ui.dialog.header>
                    <x-ui.dialog.title>Session expiring</x-ui.dialog.title>
                    <x-ui.dialog.description>
                        Your session will expire in 2 minutes. Would you like to stay logged in?
                    </x-ui.dialog.description>
                </x-ui.dialog.header>

                <x-ui.dialog.footer>
                    <x-ui.dialog.close>
                        <x-ui.button variant="outline">Log out</x-ui.button>
                    </x-ui.dialog.close>
                    <x-ui.dialog.close>
                        <x-ui.button>Stay logged in</x-ui.button>
                    </x-ui.dialog.close>
                </x-ui.dialog.footer>
            </x-ui.dialog.content>
        </x-ui.dialog>
    </section>

    <x-ui.separator />

    {{-- Sticky Footer --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sticky Footer</x-ui.typography>
        <x-ui.typography as="muted">Keep actions visible while the content scrolls.</x-ui.typography>

        <x-ui.dialog>
            <x-ui.dialog.trigger>
                <x-ui.button variant="outline">Sticky Footer</x-ui.button>
            </x-ui.dialog.trigger>

            <x-ui.dialog.content>
                <x-ui.dialog.header class="shrink-0">
                    <x-ui.dialog.title>Terms of Service</x-ui.dialog.title>
                    <x-ui.dialog.description>Please read our terms before accepting.</x-ui.dialog.description>
                </x-ui.dialog.header>

                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4 text-sm text-muted-foreground">
                    @foreach(range(1, 6) as $_)
                        <x-ui.typography as="p" class="leading-relaxed">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</x-ui.typography>
                    @endforeach
                </div>

                <x-ui.dialog.footer class="shrink-0 border-t border-border">
                    <x-ui.dialog.close>
                        <x-ui.button variant="outline">Decline</x-ui.button>
                    </x-ui.dialog.close>
                    <x-ui.dialog.close>
                        <x-ui.button>Accept</x-ui.button>
                    </x-ui.dialog.close>
                </x-ui.dialog.footer>
            </x-ui.dialog.content>
        </x-ui.dialog>
    </section>

    <x-ui.separator />

    {{-- Scrollable Content --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Scrollable Content</x-ui.typography>
        <x-ui.typography as="muted">Long content can scroll while the header stays in view.</x-ui.typography>

        <x-ui.dialog>
            <x-ui.dialog.trigger>
                <x-ui.button variant="outline">Scrollable Content</x-ui.button>
            </x-ui.dialog.trigger>

            <x-ui.dialog.content>
                <x-ui.dialog.header>
                    <x-ui.dialog.title>Scrollable Content</x-ui.dialog.title>
                    <x-ui.dialog.description>This is a dialog with scrollable content.</x-ui.dialog.description>
                </x-ui.dialog.header>

                <div class="px-6 py-4">
                    <div class="-mx-4 max-h-[50vh] overflow-y-auto px-4 space-y-4 text-sm text-muted-foreground">
                        @foreach(range(1, 10) as $_)
                            <x-ui.typography as="p" class="leading-relaxed">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</x-ui.typography>
                        @endforeach
                    </div>
                </div>
            </x-ui.dialog.content>
        </x-ui.dialog>
    </section>

</div>
</x-layouts.showcase>
