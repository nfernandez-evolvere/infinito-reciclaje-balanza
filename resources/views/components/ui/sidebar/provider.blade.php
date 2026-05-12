{{-- Sidebar provider: manages open/collapsed state --}}
{{--
    Mirrors shadcn's two-state model:
    · openMobile  — ephemeral, always starts false, never persisted (mobile only)
    · collapsed   — desktop only, persisted in cookie
--}}
<div
    x-data="{
        openMobile: false,
        collapsed:  (document.cookie.match(/sidebar_collapsed=([^;]+)/) || [,'false'])[1] !== 'false',
        isMobile:   window.innerWidth < 768,

        init() {
            const onResize = () => { this.isMobile = window.innerWidth < 768; };
            window.addEventListener('resize', onResize);
            this.$cleanup = () => window.removeEventListener('resize', onResize);
        },

        toggle() {
            if (this.isMobile) {
                this.openMobile = !this.openMobile;
            } else {
                this.collapsed = !this.collapsed;
                document.cookie = 'sidebar_collapsed=' + this.collapsed + '; max-age=' + (60 * 60 * 24 * 7) + '; path=/';
            }
        },

        closeMobile() { this.openMobile = false; },

        get isOpen()      { return this.isMobile ? this.openMobile : true; },
        get isCollapsed() { return !this.isMobile && this.collapsed; },
    }"
    @keydown.meta.b.window="toggle()"
    @keydown.ctrl.b.window="toggle()"
    {{ $attributes->twMerge('flex min-h-svh w-full') }}
>
    {{ $slot }}
</div>
