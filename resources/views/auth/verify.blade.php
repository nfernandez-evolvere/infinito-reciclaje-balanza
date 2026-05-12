<x-layouts.auth title="Verificar email">

    <x-ui.card
        x-data="{
            countdown: 45,
            canResend: false,
            resending: false,
            timer: null,
            init() { this.startCountdown() },
            startCountdown() {
                this.canResend = false
                this.countdown = 45
                clearInterval(this.timer)
                this.timer = setInterval(() => {
                    this.countdown--
                    if (this.countdown <= 0) {
                        clearInterval(this.timer)
                        this.canResend = true
                    }
                }, 1000)
            },
            resend() {
                this.resending = true
                setTimeout(() => { this.resending = false; this.startCountdown() }, 1500)
            },
            formatTime(s) {
                const m = Math.floor(s / 60)
                const sec = s % 60
                return `${m}:${sec.toString().padStart(2, '0')}`
            }
        }"
    >

        <x-ui.card.header class="items-center text-center gap-4">
            <div class="size-14 rounded-full bg-primary/10 flex items-center justify-center">
                <x-lucide-mail class="size-7 text-primary" />
            </div>
            <div class="space-y-1">
                <x-ui.typography as="h1">Verificá tu email</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Enviamos un código de 6 dígitos a
                    <span class="font-medium text-foreground">usuario@ejemplo.com</span>.
                </x-ui.typography>
            </div>
        </x-ui.card.header>

        <x-ui.card.content class="space-y-6">

            <div class="flex flex-col items-center gap-3">
                <x-ui.input-otp name="otp" :length="6" pattern="[0-9]" />
                <x-ui.typography as="muted" class="text-xs">
                    Ingresá el código sin espacios ni guiones
                </x-ui.typography>
            </div>

            <x-ui.button class="w-full">
                Verificar código
            </x-ui.button>

            <div class="space-y-1.5 text-center">
                <x-ui.typography as="muted" class="text-sm">¿No recibiste el código?</x-ui.typography>
                <p x-show="!canResend" class="text-sm text-muted-foreground">
                    Podés reenviar en
                    <span class="font-medium text-foreground tabular-nums" x-text="formatTime(countdown)">0:45</span>
                </p>
                <div x-show="canResend" x-cloak>
                    <button type="button" @click="resend" x-bind:disabled="resending"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline underline-offset-4 disabled:opacity-50 disabled:pointer-events-none">
                        <x-ui.spinner x-show="resending" x-cloak size="sm" class="text-primary" />
                        <span x-text="resending ? 'Enviando...' : 'Reenviar código'">Reenviar código</span>
                    </button>
                </div>
            </div>

        </x-ui.card.content>

        <x-ui.card.footer class="flex-col gap-3">
            <x-ui.separator />
            <a href="/examples/auth/login" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                ← Usar otro email
            </a>
        </x-ui.card.footer>

    </x-ui.card>

</x-layouts.auth>
