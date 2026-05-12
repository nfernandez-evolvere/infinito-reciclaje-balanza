<x-layouts.showcase title="Icons — Showcase">
    @php
        $svgDir = base_path('vendor/mallardduck/blade-lucide-icons/resources/svg');

        // Categorías en orden de prioridad (first match wins)
        $categoryPatterns = [
            // ── Navegación ──────────────────────────────────────────────────────
            'Flechas' => '/^(arrow|chevron|corner|move-|rotate|flip|sort-|shuffle|redo|undo|forward|backward|reply|expand|shrink|maximize|minimize|fullscreen|unfold|fold|pan|drag|step-back|step-forward|bring-to-front|send-to-back)/',

            // ── Texto & Edición ──────────────────────────────────────────────────
            'Texto' => '/^(align|bold|italic|underline|overline|strikethrough|subscript|superscript|indent|outdent|list-|type$|text-|baseline|pilcrow|heading|blockquote|wrap|letter-|a-large|a-arrow|case-|ligature|remove-formatting|whole-word|spell-check|quote$)/',

            // ── Archivos & Documentos ────────────────────────────────────────────
            'Archivos' => '/^(file|folder|book(?!mark)|bookmark|clipboard|notebook|notepad|scroll|sticky-note|save|download|upload|import|export|copy|copy-|paperclip|attachment|archive|logs|form-input|form$)/',

            // ── Tiempo ────────────────────────────────────────────────────────────
            'Tiempo' => '/^(calendar|clock|alarm|hourglass|timer|watch|time-|stopwatch|history)/',

            // ── Media & Entretenimiento ──────────────────────────────────────────
            'Media' => '/^(video|film|camera|photo|image|gallery|clapperboard|airplay|screen-share|projector|aperture|focus|flash|picture|monitor-play|tv$|tv-|closed-caption|captions|subtitles|drama|turntable|record-|rewind|fast-forward|skip|cast-|dvd|play|pause|stop-circle|repeat)/',

            // ── Audio & Música ────────────────────────────────────────────────────
            'Audio' => '/^(music|headphone|speaker|mic|volume|audio-|boom-box|cassette|drum|guitar|piano|metronome|album|disc|vinyl|bass|treble|equalizer|waveform|sound|melody|chord|note-|clef|radio)/',

            // ── Usuarios ──────────────────────────────────────────────────────────
            'Usuarios' => '/^(user|users|person|contact|circle-user|square-user|user-round|users-round|bot|baby|skull|graduation-cap|id-card|biceps-flexed|hand$|hand-|handshake|log-in|log-out)/',

            // ── Dispositivos ──────────────────────────────────────────────────────
            'Dispositivos' => '/^(monitor|laptop|tablet|smartphone|phone(?!-call|-incoming|-outgoing|-missed|-off)|keyboard|mouse|printer|server|hard-drive|usb|cpu|memory|battery|desktop|webcam|pci-card|gpu|pc-case|hdmi-port|ethernet-port|charging|scan-line|tablet-smartphone)/',

            // ── Conectividad ─────────────────────────────────────────────────────
            'Conectividad' => '/^(wifi|bluetooth|cloud|signal|network|antenna|satellite|router|rss|share-2|link(?!edin)|unlink|globe|podcast|webhook|cable|hub|modem|nfc|cast$|airplay$|broadcast|internet|connection)/',

            // ── Seguridad ─────────────────────────────────────────────────────────
            'Seguridad' => '/^(lock|unlock|key(?!board)|shield|fingerprint|eye|scan|qr-code|ban|cctv|vault|verified|biohazard|radiation|fire-extinguisher|siren|bomb-|incognito)/',

            // ── Finanzas ─────────────────────────────────────────────────────────
            'Finanzas' => '/^(bitcoin|credit-card|wallet|dollar|euro|pound-sterling|yen|franc|coins|receipt|banknote|piggy|candlestick|hand-coins|badge-dollar|badge-euro|badge-cent|badge-indian-rupee|badge-japanese-yen|badge-pound|badge-russian-ruble|badge-swiss-franc|badge-turkish-lira|indian-rupee|japanese-yen|russian-ruble|swiss-franc|turkish-lira|saudi-riyal|georgian-lari|philippine-peso|currency|decimals-arrow|ticket)/',

            // ── Compras & Logística ───────────────────────────────────────────────
            'Compras' => '/^(shopping|cart|bag(?!pipe)|store|package|gift|truck|delivery|warehouse|box|layers|stack|pallet|tag(?!s)|handbag|luggage|backpack|barrel|briefcase|container|spool|conveyor)/',

            // ── Naturaleza ────────────────────────────────────────────────────────
            'Naturaleza' => '/^(sun|moon|leaf|tree|flower|palm|grass|mushroom|clover|sprout|seeding|wheat|corn|vine|cactus|fern|shrub|earth|globe-2|globe$|terrain|wave|drop(?!s-horizontal|s-vertical)|droplets|snowflake|ice|seed|acorn|pine|bamboo|bush|herb|paw-print|feather|shell|anchor|sailboat)/',

            // ── Clima ────────────────────────────────────────────────────────────
            'Clima' => '/^(cloud-|rain|drizzle|snow|hail|sleet|fog|mist|wind|tornado|hurricane|lightning|thunder|storm|flood|drought|humidity|thermometer|haze|eclipse|sun-snow|sun-rain|umbrella)/',

            // ── Animales ─────────────────────────────────────────────────────────
            'Animales' => '/^(cat|dog|bird|fish|bug|rabbit|turtle|horse|snake|crab|snail|frog|beetle|ant|dragonfly|octopus|jellyfish|squirrel|rat|cow|pig|sheep|goat|chicken|duck|penguin|flamingo|parrot|owl|eagle|bee|butterfly|worm|lobster|shrimp|whale|shark|dolphin|deer|fox|bear|panda|lion|tiger|elephant|giraffe|koala|kangaroo|hedgehog|bat|spider|scorpion|lizard|gecko|chameleon|salamander|dino|hop$|hop-)/',

            // ── Comida & Bebida ───────────────────────────────────────────────────
            'Comida' => '/^(coffee|beer|wine|pizza|apple|cake|ice-cream|utensils|cup|bottle|candy|banana|cherry|grape|salad|croissant|cookie|popcorn|beef|drumstick|hotdog|sandwich|soup|sushi|taco|toast|lettuce|carrot|broccoli|lemon|mango|melon|orange|pear|peach|plum|strawberry|watermelon|avocado|potato|onion|garlic|pepper|tomato|cucumber|pumpkin|bread|baguette|muffin|donut|pretzel|waffle|pancakes|bacon|cheese|milk|juice|tea|cocktail|martini|glass-water|chef-hat|cooking-pot|ham|hamburger|dessert|lollipop|popsicle|bean|citrus|vegan|dessert|citrus)/',

            // ── Mapas & Transporte ────────────────────────────────────────────────
            'Mapas' => '/^(map|compass|location|pin|marker|navigation$|plane|car|bus|train|ship|bike|bicycle|route|road|mountain|landmark|flag|parking|fuel|airport|station|dock|bridge|tunnel|tram-|scooter|kayak|ferry|helicopter|van|tractor|traffic-cone|signpost|milestone|footprints|rail-symbol|boat|drone)/',

            // ── Salud ─────────────────────────────────────────────────────────────
            'Salud' => '/^(heart(?!-handshake|-in-hand|-off|-crack)|stethoscope|pill|syringe|hospital|ambulance|bandage|tooth|brain|dna|virus|bacteria|pulse|activity$|heart-pulse|dumbbell|run|walk|stretch|weight|sleep|scan-face|ear$|ear-off|eye-off|hand-heart|hand-helping|helping-hand|life-buoy)/',

            // ── Hogar & Muebles ───────────────────────────────────────────────────
            'Hogar' => '/^(home|house|door|bed|sofa|armchair|lamp|blinds|air-vent|refrigerator|heater|fan|washing-machine|toilet|towel-rack|shower-head|shelving-unit|soap-dispenser|couch|wardrobe|kitchen|bath|oven|microwave|dishwasher|blender|toaster|kettle|iron)/',

            // ── Edificios ─────────────────────────────────────────────────────────
            'Edificios' => '/^(building|apartment|office|factory|mall|market|hotel|school|university|library|museum|theater|cinema|stadium|church|mosque|temple|castle|fort|tower|lighthouse|arch|wall|fence|gate|garage|shed|tent|cabin|barn|silo|greenhouse|construction|brick-wall|land-plot|dam)/',

            // ── Diseño & UI ───────────────────────────────────────────────────────
            'Diseño' => '/^(palette|crop|frame|grid|layout|proportions|scaling|shapes$|vector|lasso|section|columns|rows|blend|component|blocks|combine|group$|ungroup|layer|canvas|artboard|viewport|ruler|drafting-compass|highlighter|sticker|mirror-|inspect|swatch|grab|pointer|selection|sidebar$|sidebar-|panel|app-window|kanban|table-of|workflow|form|view|split|template)/',

            // ── Herramientas ──────────────────────────────────────────────────────
            'Herramientas' => '/^(settings|wrench|tool|hammer|screwdriver|scissors|pen|pencil|eraser|brush|paintbrush|paint|spray|dropper|pipette|knife|axe|shovel|pick|chisel|saw|drill|grinder|level|measure|needle|thread|craft|flask|beaker|scale|magnet|lens|prism|atom|anvil|gavel|cog|wand|edit|delete|trash|filter|search|zoom|import$|export$|download$|upload$|refresh|reload|reset|replace|find|highlight|cut|paste|undo$|redo$|history$|repair|fix|patch|bolt|zap|plug|power|generator|battery-charging|sliders|calculator|binoculars|test-tube|locate|waypoints)/',

            // ── Energía & Electrónica ─────────────────────────────────────────────
            'Energía' => '/^(zap|bolt|plug|power|ev-charger|solar-panel|utility-pole|hdmi|ethernet|cable-car|charger|voltage|circuit|fuse|switch-|electricity|generator|turbine)/',

            // ── Código & Dev ──────────────────────────────────────────────────────
            'Código' => '/^(code|terminal|console|command|git|branch|commit|merge|fork|bug|debug|variable|binary|function|class|method|hash|brackets|operator|regex|webhook$|api|braces|parentheses|curly|angle-brackets|ampersand|asterisk|equal|diff|slash$|slash-|option$|iteration-)/',

            // ── Gráficos & Data ───────────────────────────────────────────────────
            'Gráficos' => '/^(chart|bar-chart|pie-|line-chart|area-chart|scatter|radar|gauge|graph|trend|analytics|stat|histogram|heatmap|treemap|funnel|gantt|metric|kpi|dashboard|database|data-|table$|table-|kanban-square|presentation|report|logs$|tally-)/',

            // ── Juegos & Ocio ─────────────────────────────────────────────────────
            'Juegos' => '/^(gamepad|joystick|chess|dice|sword|puzzle|trophy|medal|award|target|volleyball|ferris-wheel|roller-coaster|party-popper|balloon|drama|toy-brick|telescope|sport|bow-arrow)/',

            // ── Formas & Símbolos ─────────────────────────────────────────────────
            'Formas' => '/^(circle$|circle-|square$|square-|triangle$|hexagon|diamond$|star$|star-|pentagon|octagon|rectangle|ellipse|rhombus|cube|cylinder|cone|sphere|torus|cross|plus$|minus$|divide|multiply|infinity|pi$|sigma|radical|diameter|radius|tangent|spline|orbit|astroid|squircle|cuboid|axis-3d|pyramid|shapes$)/',

            // ── Estado & Notificaciones ───────────────────────────────────────────
            'Estado' => '/^(check|circle-check|square-check|badge-check|thumbs|smile|frown|meh|laugh|angry|badge$|badge-alert|badge-help|badge-info|badge-minus|badge-plus|badge-x|badge-question|badge-percent|bell|notification|alert|info$|help-circle|x-circle|x-octagon|x-square|x$|ban$|siren$|flag$|ribbon|crown|gem|verified$|award$|star-off|loader|loader-|triangle-alert|minus-circle|minus-square|plus-circle|plus-square|check-check|vote|sparkle|goal|trophy$|medal$)/',

            // ── Mensajería ────────────────────────────────────────────────────────
            'Mensajería' => '/^(message|mail|inbox|send|chat|comment|mention|at-sign|megaphone|bullhorn|news|post|phone-call|phone-incoming|phone-outgoing|phone-missed|reply-|speech|envelope|voicemail|concierge-bell)/',

            // ── Social & Marcas ───────────────────────────────────────────────────
            'Social' => '/^(github|twitter|facebook|instagram|linkedin|youtube|twitch|slack|discord|figma|vercel|chrome|chromium|dribbble|behance|codepen|codesandbox|framer|gitlab|bitbucket|notion|jira|asana|trello)/',

            // ── Zodíaco ───────────────────────────────────────────────────────────
            'Zodíaco' => '/^zodiac/',

            // ── Accesibilidad ─────────────────────────────────────────────────────
            'Accesibilidad' => '/^(accessibility|wheelchair|hearing|braille|contrast|ear-|eye-off|mars|venus|transgender|non-binary|gender|male|female|intersex)/',

            // ── Misceláneo ────────────────────────────────────────────────────────
            'Misceláneo' => '/.*/',
        ];

        $icons = [];
        $grouped = array_fill_keys(array_keys($categoryPatterns), []);

        foreach (glob("{$svgDir}/*.svg") as $file) {
            $name = basename($file, '.svg');
            $svg = preg_replace('/>\s+</', '><', trim(file_get_contents($file)));
            $icons[$name] = $svg;

            foreach ($categoryPatterns as $cat => $pattern) {
                if (preg_match($pattern, $name)) {
                    $grouped[$cat][$name] = $svg;
                    break;
                }
            }
        }

        // Eliminar categorías vacías y ordenar iconos dentro de cada una
        $grouped = array_filter($grouped, fn($g) => count($g) > 0);
        foreach ($grouped as &$group)
            ksort($group);
        ksort($icons);
    @endphp

    <script>
        window.__lucideIcons = @json($icons);
        window.__lucideGroups = @json(array_map('array_keys', $grouped));
    </script>

    <div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-10 space-y-8" x-data="{
        search: '',
        copied: null,
        activeCategory: 'all',
        _timer: null,

        categories: Object.keys(window.__lucideGroups),
        groups: window.__lucideGroups,

        get filtered() {
            const q = this.search.toLowerCase();
            let names = this.activeCategory === 'all'
                ? Object.keys(window.__lucideIcons)
                : (this.groups[this.activeCategory] ?? []);
            if (q) names = names.filter(n => n.includes(q));
            return names;
        },

        svg(name) { return window.__lucideIcons[name] ?? ''; },

        copy(name) {
            navigator.clipboard.writeText('<x-lucide-' + name + ' />');
            this.copied = name;
            clearTimeout(this._timer);
            this._timer = setTimeout(() => { this.copied = null; }, 2000);
        },

        selectCategory(cat) {
            this.activeCategory = cat;
            this.search = '';
        }
    }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1">
                <x-ui.typography as="h1">Icons</x-ui.typography>
                <x-ui.typography as="muted" class="mt-1 max-w-prose">
                    {{ count($icons) }} iconos de Lucide.
                    Clic en cualquier ícono copia <x-ui.typography as="code">&lt;x-lucide-name /&gt;</x-ui.typography>
                    al portapapeles.
                </x-ui.typography>
            </div>
            <div>
                <x-ui.input type="text" x-model.debounce.200ms="search" placeholder="Buscar icono…"
                    class="w-full sm:w-72">
                    <x-slot:leading><x-lucide-search /></x-slot:leading>
                </x-ui.input>
            </div>
        </div>

        {{-- Category tabs --}}
        <div class="flex gap-1.5 flex-wrap">
            <button type="button" @click="selectCategory('all')"
                :class="activeCategory === 'all' ? 'bg-primary text-primary-foreground' : 'bg-card text-muted-foreground hover:text-foreground hover:bg-accent border border-border'"
                class="inline-flex h-7 items-center rounded-full px-3 text-xs font-medium transition-colors">
                Todos <span class="ml-1.5 opacity-60 tabular-nums">{{ count($icons) }}</span>
            </button>
            @foreach($grouped as $cat => $catIcons)
                <button type="button" @click="selectCategory('{{ $cat }}')"
                    :class="activeCategory === '{{ $cat }}' ? 'bg-primary text-primary-foreground' : 'bg-card text-muted-foreground hover:text-foreground hover:bg-accent border border-border'"
                    class="inline-flex h-7 items-center rounded-full px-3 text-xs font-medium transition-colors">
                    {{ $cat }} <span class="ml-1.5 opacity-60 tabular-nums">{{ count($catIcons) }}</span>
                </button>
            @endforeach
        </div>

        {{-- Stats --}}
        <div class="flex items-center gap-3 min-h-6">
            <x-ui.typography as="muted" class="text-xs tabular-nums">
                <span x-text="filtered.length"></span> iconos
            </x-ui.typography>
            <template x-if="copied">
                <x-ui.badge state="success" :subtle="true">
                    <x-lucide-check class="size-3" />
                    <span x-text="`<x-lucide-${copied} /> copiado`"></span>
                </x-ui.badge>
            </template>
        </div>

        {{-- Empty state --}}
        <template x-if="filtered.length === 0">
            <x-ui.empty-state title="Sin resultados">
                <x-ui.typography as="muted">
                    No hay íconos que coincidan con "<span x-text="search" class="text-foreground"></span>"
                </x-ui.typography>
            </x-ui.empty-state>
        </template>

        {{-- Grid --}}
        <div class="grid grid-cols-[repeat(auto-fill,minmax(88px,1fr))] gap-1.5">
            <template x-for="name in filtered" :key="name">
                <button type="button" @click="copy(name)"
                    :class="copied === name ? 'border-success bg-success-subtle' : 'border-transparent bg-card hover:border-border hover:bg-accent/40'"
                    class="group flex flex-col items-center gap-2 rounded-lg border p-3 text-center transition-all focus:outline-none focus:ring-2 focus:ring-ring/30"
                    :title="name">
                    <span class="size-5 shrink-0 pointer-events-none [&>svg]:size-5 [&>svg]:stroke-current"
                        :class="copied === name ? 'text-success' : 'text-foreground'" x-html="svg(name)"></span>
                    <span class="w-full truncate text-[10px] leading-tight pointer-events-none"
                        :class="copied === name ? 'text-success font-medium' : 'text-muted-foreground group-hover:text-foreground'"
                        x-text="name"></span>
                </button>
            </template>
        </div>

    </div>
</x-layouts.showcase>