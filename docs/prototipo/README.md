# Infinito Reciclaje — Sistema de Gestión de Balanza

Design system for the **Sistema de Gestión de Balanza** built for **Infinito Reciclaje / Municipalidad de Corrientes, Argentina**. The product is a web application used at the entrance of a municipal final-disposal site to register, control and analyse the in/out flow of waste-collection trucks.

## Sources

- **Specification document** (provided in chat): full functional brief, screen list, business rules and visual palette. This is the single source of truth for this iteration — no Figma file, design system, or production codebase was attached. Every visual decision in this kit was derived from the spec and the operational context (operator under time pressure, admin needing oversight at a glance).
- **Production stack** (described, not provided): Laravel Blade + SQL Server. This system is the HTML/CSS prototype layer used to validate UX before production work begins.

> ⚠️ **No fonts, logos, or icon assets were supplied.** Inter is loaded from Google Fonts (per spec). Lucide icons are used as the icon system — see ICONOGRAPHY. A logo placeholder is included; please provide the official Infinito Reciclaje mark when available.

---

## Product Context

**Client:** Infinito Reciclaje — operator of the municipal disposal site for the Municipality of Corrientes, Argentina.

**Goal:** Replace paper/spreadsheet workflows at the truck scale ("balanza") with a digital system that captures every weighing event with minimal data entry from the operator.

**Users:**

| Role | Persona | Context |
| --- | --- | --- |
| Operator | **Roberto** | Sits in a small booth at the scale. Under pressure with 8+ trucks/hour. Must not type anything the system already knows. |
| Administrator | **Nacho** | Manages master data (vehicles, orígenes, services). Reads dashboards and exports reports. |

**Design philosophy:** *Máxima automatización, mínima intervención manual.* Every typed field is a chance to make a mistake; the operator's job is to confirm, not to write.

---

## PROTOTYPE INVENTORY

What the current click-through prototype actually does. Demo credentials are visible on the login screen: `roberto / Evolvere123!@` → operator flow, `nacho / Evolvere123!@` → admin flow.

### 1 · Login

- Single-card login. Visible demo credentials for both roles for validation purposes (must be removed before production).
- Role-based redirect: operator → Balanza, admin → Dashboard.
- Inline error on bad credentials (`Usuario o contraseña incorrectos.`).

### 2 · Operator shell

- Sticky header with brand, **live clock** (updates every second), connection status pill (`En línea` with green dot), user chip with role, logout icon button.
- **Inline operator nav** in the header — only two buttons (`Pesaje` / `Historial`) since the operator only ever switches between these two screens; no side rail.
- **Sticky footer status bar** showing the last pesaje recorded (`patente · neto · hora`) and shift totals (count + tonnes). Always visible while working.

### 3 · Balanza (pesaje) — the operator's main screen

This is the most-used screen in the system. Roberto opens it on shift start and rarely leaves.

- **Vertical sequential flow**: vehicle → service → weight → summary → action bar. One card per step, top-to-bottom; no left-right eye movement.
- **Numbered step indicators (1 → 2 → 3)** that fill with a green check when the step is complete; later steps are visually dimmed until reachable.
- **Step 1 · Vehículo**
  - Free-text input that matches against `patente` or `interno` from the padrón.
  - Autocomplete popper with up to 6 matches, each showing patente, internal number, vehicle type, tara and titular.
  - Enter selects the first match.
  - Once selected, four read-only badges appear: `Tara`, `Tipo`, `Titular`, `Interno`.
- **Step 2 · Tipo de servicio**
  - Native `<select>` of the five services.
  - **Cascade as warning, never override** — picking a service auto-fills the *origen* and shows the *tipo habitual* as a blue informational badge. If the selected vehicle's actual type differs from the service's habitual type, a soft orange warning block appears (*"No es el tipo habitual para este servicio…"*) but the vehicle's type is preserved.
- **Step 3 · Peso bruto**
  - **72 px digital-style readout** with the input on the left, live `Tara` and `Neto estimado` on the right of the same shell.
  - Border + value colour change with validation state: green when in habitual range, orange when out of range.
  - Range hint always visible underneath (`Rango habitual Compactador: 10.000 – 26.500 kg.`).
  - Out-of-range never blocks; it only flags.
- **Summary card** — appears highlighted in `--green-50` when the form is complete. Four columns × two rows: vehicle, service, origen, type / bruto, tara, neto, operator.
- **Sticky action bar** at the bottom of the page with `Limpiar` (Esc), a contextual hint of the next thing to do, and the primary `Guardar pesaje` CTA (Ctrl+S).
- **Keyboard shortcuts**
  - `↵` advances to the next field (or selects the first autocomplete match).
  - `Ctrl+S` / `⌘+S` saves when valid.
  - `Esc` clears the form.
  - Visible hint chips show all three shortcuts at the top of the page; the save button echoes its shortcut on its right edge.
- **Save success** — full-screen toast with an animated check ring; auto-dismisses in 1.1 s; form resets and focus returns to the vehicle input.

### 4 · Historial (operator)

- Four small KPIs at the top: pesajes count, total net tonnes, average per trip, **camiones en predio** (count of records still open).
- Table of the shift with: entrada · salida · estado (pill: `En predio` / `Cerrado`, with a blue `Editado` pill when applicable) · patente · servicio · origen · bruto · tara · neto.
- **Per-row actions** for the operator:
  - **Marcar egreso** *(visible only when the record is `En predio`)* — opens a modal that captures the current time and an optional outgoing weight. Confirming flips the record to `Cerrado` and writes an `egreso` entry to the change log.
  - **Editar** — opens the same edit modal as the admin uses, with a mandatory `motivo`. Every changed field produces an immutable log entry signed with the operator's username. The admin sees these entries in the Pesajes screen alongside their own edits.
  - **Ver historial** — read-only modal listing every prior change for that pesaje. Dimmed when the record has no history.
- Tabular numerics on every numeric column.
- Empty state copy: *"Sin pesajes en este turno todavía."*

### 5 · Admin shell

- **Accordion sidebar** grouped in six sections:
  - **Operación** — Dashboard · Pesajes
  - **Transporte** — Vehículos · Tipos de vehículo
  - **Orígenes** — Orígenes
  - **Servicios** — Tipos de servicio
  - **Sistema** — Usuarios
  - **Análisis** — Reportes
- Section headers are clickable to expand/collapse with a rotating chevron. The section containing the active screen is auto-expanded on navigation.
- Footer of the sidebar shows the admin's avatar, name, role and a logout icon button.

### 6 · Dashboard (admin)

- **Alert banners** at the top: gap-in-records and unusual-weight alerts, each with a `Revisar` ghost button.
- **Camiones en el predio** — small section that only appears when records are open. Shows the count + a table with patente, tipo, servicio, origen, hora de entrada, neto registrado, operador. This is Nacho's at-a-glance "who is still inside" view.
- **KPIs del día** (4 cards): pesajes, toneladas, promedio por viaje, horas operativas — each with a "vs. promedio" delta.
- **KPIs del mes** (3 smaller cards): pesajes acumulados, toneladas acumuladas, días operativos.
- **Evolución diaria** (Chart.js bar chart, 7 days): today highlighted in `--green-700`, prior days in a light green; dashed average line; native Inter-styled tooltip.
- **Por origen** table: pesajes, toneladas, kg/ha per origen.
- **Por tipo de vehículo** table: viajes, toneladas, and a horizontal `pct` bar.

### 7 · Pesajes (admin) — full filterable log

- Five filters: search (patente/ID), **estado** (`Todos` / `En predio` / `Cerrado`), origen, service, operator.
- Header summary shows filtered count + total net tonnes in the current view.
- Table columns: ID, entrada, salida, estado, patente, servicio, origen, bruto, tara, neto, operador. Edited rows carry a blue `Editado` pill next to the state pill.
- Per-row actions:
  - **Marcar egreso** (only when `En predio`) — same modal as the operator.
  - **Editar** — opens the edit modal with mandatory motivo; saving writes one log entry per modified field, signed with the admin's username.
  - **Ver historial** — read-only modal listing every prior change as `campo · anterior → nuevo · motivo · usuario · fecha`. Strikethrough on old values, green on new ones. Dimmed when no log exists.
- Export Excel button in the page header (visual; non-functional in the prototype).

### 8 · ABMs — five screens

- **Vehículos** — table with search, status pills, and an "Agregar vehículo" modal (patente, número interno, tipo, tara, titular, capacidad, observaciones, active/inactive toggle).
- **Orígenes** — table with hectáreas and barrios columns plus total in the header lede; modal for new origen; per-origen service assignment with turnos and schedules.
- **Tipos de servicio** — table with the cascade rule (`tipoSugerido`) visible and editable; modal for new service.
- **Tipos de vehículo** — table with rangeMin/rangeMax in kg per type; an info banner reminds that these ranges are advisory, never blocking.
- **Usuarios** — table with avatar+name composite cell, role pill (Admin / Operador), turn, status; modal with usuario, nombre completo, rol, turno, contraseña inicial; per-row actions for edit, password reset, deactivate.
- Every ABM follows the same pattern: search/filter row at the top, table with right-aligned ghost icon actions, "Agregar" primary CTA in the header.

### 9 · Reportes (admin)

- Filter card: período (desde/hasta), origen, tipo de servicio, tipo de vehículo.
- Active filters echo as gray pills under the filters.
- `Generar reporte` reveals the preview: 4 summary KPIs, bar chart, by-origen table, by-vehicle-type table, and a "Densidad de generación" horizontal bar viz (kg/ha per origen).
- Export buttons for PDF and Excel (visual).
- Empty-state card before generation with a chart icon and copy: *"Aplicá los filtros y generá el reporte para ver la vista previa."*

### Cross-cutting

- **Argentine locale formatting** end to end — `8.500 kg`, `142,5 t`, `dd/mm/yyyy`.
- **Tabular numerics** on every numeric column, KPI value and weight readout.
- **In-memory state only** — saved pesajes append to the in-session list; ABM rows persist for the session; nothing survives a reload.

---

## CONTENT FUNDAMENTALS

The voice is **operational Argentine Spanish** — short, direct, no hedging. The product talks like a colleague at the scale booth, not a marketing site or a corporate enterprise tool.

### Language rules

- **Spanish (es-AR)** throughout. Argentine conventions: `Patente` (not `Matrícula`), `Camión` (not `Vehículo` when speaking of trucks), `Tara`, `Pesaje`, `Predio`.
- **Decimals with comma**, thousand separators with period: `8.500 kg`, `142,5 t`.
- **24-hour time:** `14:32`. Dates `dd/mm/yyyy`.
- **Units always present** and lower-case: `kg`, `t`, `t/viaje`, `kg/hectárea`.
- **Voseo (vos)** is used for short imperatives directed at the user: `Seguí los tres pasos`, `Ingresá el peso`, `Generá el reporte`. Labels and noun-form chrome stay third-person (`Peso bruto`, `Último pesaje del turno`, `Tipo de servicio`) — voseo is the verb register only, never used for static labels. Avoid colloquial-permission phrasing like *"igual podés guardar"*; prefer the neutral *"la validación no bloquea el guardado"*.

### Casing

- **Sentence case** for labels, headers and buttons (`Guardar pesaje`, not `Guardar Pesaje` or `GUARDAR PESAJE`). The single exception: the primary CTA on the Balanza screen may render in **uppercase** for emphasis — `GUARDAR PESAJE` — because Roberto reaches for it 40+ times per shift and it must be unmistakable.
- **Title case is avoided.** Spanish doesn't use English-style Title Case in body copy and forcing it makes the UI feel translated.
- **All-caps** is reserved for: the primary save CTA, status pills (`ACTIVO`, `INACTIVO`), and the four-letter unit tag in tables.

### Tone examples

| Where | Copy | Why |
| --- | --- | --- |
| Vehicle autocomplete badge | `Tara: 8.500 kg · Tipo: Compactador · Titular: Municipalidad de Corrientes` | Dot-separated facts, no labels-as-sentences. |
| Validation, in-range | *(no message — green border is enough)* | Don't praise correct input. |
| Validation, out-of-range | `Fuera del rango habitual para Compactador (10.000 – 26.500 kg). La validación no bloquea el guardado.` | Soft, non-blocking, names the rule. Neutral register — no colloquial permission. |
| Save success | `Pesaje guardado` | Two words, plus the check animation. |
| Dashboard alert | `Gap en registros 12:30 – 12:45` | States the fact. No "We detected that…". |
| Empty state | `Sin pesajes en este turno todavía.` | Friendly, ends in a period, no exclamation. |

### What we don't do

- **No emoji in product copy.** The spec mentions a `⚠️` glyph in alerts — we render that as a Lucide `alert-triangle` icon, not the emoji.
- **No exclamation marks** outside genuine errors. The system is a colleague, not a cheerleader.
- **No first-person plural** ("vamos a guardar tu pesaje"). The system narrates state, it doesn't act with you.
- **No microcopy filler** ("¡Listo!", "¡Genial!"). If a state is good, show it visually and move on.

---

## VISUAL FOUNDATIONS

The visual system is **utilitarian and quiet**. This UI runs in a humid booth on a 1280-wide screen for 8 hours at a stretch. It must read at a glance, age gracefully under fluorescent light, and never compete with the operator's attention.

### Palette

| Token | Hex | Role |
| --- | --- | --- |
| `--green-700` | `#2E7D32` | Primary action, OK states, brand accent. |
| `--green-50`  | `#E8F5E9` | Confirmation card backgrounds, success-light surfaces. |
| `--orange-700`| `#F57C00` | Warnings, soft anomalies, non-blocking validation. |
| `--orange-50` | `#FFF4E5` | Warning surface tint. |
| `--red-700`   | `#C62828` | Hard errors, destructive states. |
| `--blue-700`  | `#1565C0` | Informational accents, links, neutral badges. |
| `--blue-50`   | `#E3F2FD` | Info surface tint. |
| `--ink-900`   | `#212121` | Primary text. |
| `--ink-700`   | `#424242` | Secondary text. |
| `--ink-500`   | `#757575` | Muted, labels, placeholders. |
| `--ink-300`   | `#BDBDBD` | Disabled, dividers on light bg. |
| `--line`      | `#E0E0E0` | Standard divider. |
| `--bg`        | `#F5F5F5` | Page background. |
| `--surface`   | `#FFFFFF` | Cards, panels, inputs. |

The palette is **flat Material-700/50 pairs**. No mid-tones, no tertiary brand colour, no gradients.

### Type

- **Family:** Inter (Google Fonts), weights 400 / 500 / 600 / 700.
- **Tabular numbers** (`font-feature-settings: "tnum"`) are mandatory on every numeric column, KPI value and weight readout. Roberto compares weights vertically; columns must line up.
- **Display weight is 700**, headings 600, body 400, labels 500. We don't use weight 300 or italic.
- Sizes are on a 4 px grid: 12 / 14 / 16 / 18 / 20 / 24 / 32 / 48. The weight readout on the Balanza screen is the only thing in the 48px tier.

### Spacing

- **4 px base unit.** Allowed steps: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64.
- **Card padding:** 24 px. **Form field gap:** 16 px between fields, 8 px between label and input.
- **Generous over tight** — this is the spec's word. Two adjacent dense regions are never allowed; cards always have at least 16 px of breathing room around them.

### Surfaces & elevation

- **Page background:** `--bg` (#F5F5F5).
- **Cards:** white surface, 8 px radius, single shadow `0 2px 8px rgba(0,0,0,0.08)`. No second elevation tier — modals use a backdrop scrim, not a deeper shadow.
- **Inputs:** white surface, 6 px radius, 1 px solid `--line`. Focus ring is 2 px `--green-700` with 1 px white inset; no glow.
- **Borders before shadows.** When two elements stack, prefer a 1 px line over a second shadow.

### Backgrounds & imagery

- **No background imagery.** No textures, gradients, illustrations, full-bleed photography, or hand-drawn motifs anywhere in the product UI. The page background is a flat `#F5F5F5`.
- **No dark mode.** The booth has fluorescent light; light mode reads better at distance.
- A subtle 1 px hairline at the bottom of the app header is the only chrome decoration.

### Borders & radius

- **Cards:** 8 px.
- **Inputs, buttons, badges:** 6 px.
- **Pills (status):** fully rounded.
- **Border colour:** always `--line` for neutrals, semantic-700 for state-coloured borders (validation feedback).

### Shadows

| Token | Value | Use |
| --- | --- | --- |
| `--shadow-card` | `0 2px 8px rgba(0,0,0,0.08)` | Default for every card and the app header. |
| `--shadow-modal`| `0 16px 48px rgba(0,0,0,0.18)` | Modal dialogs only. |
| `--shadow-pop`  | `0 4px 16px rgba(0,0,0,0.12)` | Dropdowns and autocomplete poppers. |

No inner shadows. No coloured shadows.

### Motion

- **Easing:** `cubic-bezier(0.2, 0, 0, 1)` (a Material-style emphasized ease-out) for everything that moves.
- **Durations:** 120 ms for hovers and press states, 180 ms for screen-level transitions, 320 ms for the save-success check (the only "celebratory" animation in the system).
- **No bouncing, no parallax, no scroll-linked motion.** A weighing log is not a marketing site.
- **Reduced motion** disables every transition except color changes.

### Hover & press states

- **Buttons, primary:** hover darkens `--green-700` → `#256528` (about −6% L). Press shifts 1 px down via `transform: translateY(1px)`. No scale.
- **Buttons, secondary:** hover shows a `--bg`-tinted fill on a previously transparent background.
- **Table rows:** hover paints `--bg` (#F5F5F5) full-width. Active selection paints `--blue-50` with a 2 px left border in `--blue-700`.
- **Icon buttons:** hover paints a 32×32 px circle in `--bg`. No tooltips for obvious icons; tooltips only when an icon represents a non-obvious action.

### Transparency & blur

- **None.** No glassmorphism, no backdrop-filter. The booth screen and projector both render blur poorly.
- The only transparent element is the modal scrim, which is `rgba(33, 33, 33, 0.48)`.

### Layout

- **1280 px target width**, 1024 px graceful minimum. Above 1440 px, content centers with a max-width — it doesn't stretch.
- **Two layouts:**
  - **Operator (Balanza, Historial):** full-width single-column form with a footer status bar fixed at the bottom of the viewport. No side nav — Roberto only switches between two screens.
  - **Admin (Dashboard, ABMs, Reportes):** 240 px left rail navigation, fluid content area, no right rail.
- **Grid:** 12-column inside the admin content area, 24 px gutters, 32 px page padding.

### Fixed elements

- **App header:** 56 px, sticky top, white surface, hairline bottom border.
- **Operator footer bar:** 48 px, sticky bottom, white surface, hairline top border. Shows last pesaje and shift totals.
- **Admin sidebar:** sticky left, full viewport height.

### Status & semantic visualization

- **Status pills** are 24 px tall, fully rounded, 8 px horizontal padding, weight 600 uppercase letters at 11 px with 0.04 em tracking.
- **Validation borders** override input borders when active: green when in-range, orange when out-of-range.
- **Banners** (the dashboard alert strip) use the semantic-50 surface with a 1 px semantic-700 border on the left edge only — the spec's lone use of a left-coloured accent, reserved for transient alerts, never for static content cards.

---

## ICONOGRAPHY

No icon assets were provided. We adopt **[Lucide](https://lucide.dev)** as the icon system.

- **Why Lucide:** open-source, MIT-licensed, large coverage including transport (`truck`), scale (`scale`), filtering (`filter`), and data viz (`bar-chart`). Stroke-based, 1.5 px stroke, square 24 × 24 viewBox — matches the calm, utilitarian palette.
- **Delivery:** loaded from CDN via the `lucide@latest` global script. Icons appear as `<i data-lucide="…"></i>` placeholders that the runtime swaps for inline SVG. No icon-font dependency.
- **Sizes:** 16 px (inline with body text), 20 px (inside buttons and input adornments), 24 px (sidebar nav and standalone). Never larger — large hero icons aren't part of this product's vocabulary.
- **Colour:** icons inherit `currentColor`. No multi-tone icons.
- **No emoji** in production copy. The spec's `⚠️` is rendered as a Lucide `alert-triangle`. The spec's `✅` / `⚫` for status cells are rendered as a coloured filled dot + label (`ACTIVO` / `INACTIVO`).
- **No unicode pictographs** (✓, ✗, ▶ etc.) — always Lucide.
- **No PNG icons.**

### Logo

The Infinito Reciclaje mark was not provided. `assets/logo-placeholder.svg` is a wordmark stand-in built from Inter weight 700 plus a Lucide `recycle` glyph in `--green-700`. **Replace with the official mark when available.**

---

## DECISIONS LOG

Choices taken during the iteration rounds. Each entry has the **decision**, the **option chosen**, the **alternatives considered**, and a brief **rationale** so future contributors don't re-litigate or quietly drift.

### Information architecture (Ronda 1)

| # | Decision | Chosen | Considered | Rationale |
| --- | --- | --- | --- | --- |
| IA-1 | Numbered step indicators on the Balanza form | **Keep them (1 → 2 → 3 with check on completion)** | Strip them for a flat form; keep visual separators without numbers | New operators need scaffolding for the first weeks. Once they internalise the flow, the numbers fade into peripheral vision. |
| IA-2 | Layout of the Balanza screen | **Single vertical column, form on top, summary below, sticky action bar** | 2-column (form + summary side-by-side); summary as sticky overlay; no summary at all | Reduces left-right eye movement under time pressure. Summary as the *final* card reinforces the sequence rather than competing with it. |
| IA-3 | Size of the weight input | **72 px, scale-style readout, dominant on the page** | 28 px input; 48 px input; dual display (small input + huge readout above) | Roberto must read this from across the booth and confirm at a glance. The readout is the climax of the form — it deserves the most visual weight. |
| IA-4 | Cascade *servicio → tipo de vehículo* | **Warning when mismatch, never override** | Silent override (replace vehicle's actual type with the service's habitual type); no cascade at all | The padrón's `tipo` is ground truth. The cascade is advisory. Overriding silently can cause data quality issues in reports; warning preserves both signal and operator agency. |
| IA-5 | Admin maestros (ABMs) | **Five ABMs: Vehículos, Orígenes, Tipos de servicio, Tipos de vehículo, Usuarios** | Only Vehículos (the spec's explicit ask); add Orígenes only | Spec uses "ABMs" in plural and the dashboard needs hectáreas, services with their cascade rules, and weight ranges as configurable data — leaving them hardcoded blocks day-2 changes. Usuarios is required for production once the demo creds are removed. |
| IA-6 | Audit/edit of individual pesajes by admin | **Full editable list with mandatory motivo + immutable change log per record** | Read-only list with export only; no admin pesaje view (corrections via SQL) | Corrections are inevitable (typos, wrong service). Editing in-product with a forced motivo plus a permanent audit trail keeps data integrity while removing the SQL-Server-direct workaround. |
| IA-7 | Live monitor of the balanza for admin | **Not yet** | Live widget on dashboard; dedicated big-screen "Monitor" page; both | Dashboard refresh on demand is enough for v1. Live monitoring is a fast-follow once the basics are validated; don't bloat the surface area before users ask for it. |
| IA-8 | Admin sidebar organisation | **Accordion with six groups: Operación · Transporte · Orígenes · Servicios · Sistema · Análisis** | Flat list ordered by usage; single "Padrones" group for all ABMs | Matches the client's existing taxonomy: transport items under Transporte, geographic areas as Orígenes, service types as Servicios. Accordion keeps the active group expanded and the rest tucked away. The group containing the current screen auto-expands on nav. |
| IA-9 | Keyboard shortcuts for the operator | **Yes, with visible hints — Enter advances, Ctrl+S saves, Esc clears** | None (mouse only); shortcuts but no visible hints | At 8+ trucks/hour, every mouse trip is dead time. Visible hints serve as in-context training for Roberto and a memory aid forever after. |
| IA-10 | Operator correction of own pesajes *(out-of-scope in spec; closed in ronda 1)* | **F.2 — Same edit modal as the admin, with mandatory `motivo` and immutable per-field log shared with admin.** Visible on every row of the operator's `Historial`. | F.1 free edit within shift; F.3 "anular último" within N minutes; F.4 no operator correction. | Roberto's typos can't wait for Nacho. Going through the same audited path preserves traceability across roles; a single log model means admin sees operator edits and vice versa. |
| IA-11 | Egreso flow *(out-of-scope in spec; closed in ronda 1)* | **G.2 — Egreso as a timestamp mark, with an *optional* outgoing weight captured for audit only.** Each pesaje now has a state machine `En predio` → `Cerrado`. `Marcar egreso` button on operator `Historial` and admin `Pesajes`. The optional `brutoSalida` is stored but **not** used in the neto calculation; neto remains `bruto_entrada − tara`. | G.1 no egreso; G.3 full double weighing where neto = `bruto_entrada − bruto_salida`. | Operationally the predio uses single-weighing with stored tara, so neto stays correct. Capturing time-of-exit (and optionally the outgoing weight) unlocks dwell-time KPIs, the "Camiones en el predio" widget, and an evidence trail without rewriting the data model. Whether neto eventually moves to G.3 is parked as an open question. |

### UX writing & content (Ronda 2 — pending)

Items below are **proposed for ronda 2** but were applied tactically during ronda 1 to keep the prototype consistent. They will be revisited and signed off with Nacho / Roberto in the next round.

| # | Decision | Chosen | Considered | Rationale |
| --- | --- | --- | --- | --- |
| UX-1 | Voseo (Argentine *vos*) in UI verbs | **Used for short imperatives to the user** (`Seguí`, `Ingresá`, `Generá`) | Avoid voseo entirely, use third-person / infinitive imperative everywhere | Es-AR voseo is the natural register for an operational tool in Corrientes. Verb-only — labels and chrome stay third-person noun form for legibility. |
| UX-2 | Validation copy register | **Neutral declarative** (*"La validación no bloquea el guardado."*) | Colloquial-permissive (*"Igual podés guardar."*); silent (border colour only) | A municipal system is a colleague, not a friend. The permissive register subtly implies "we're letting you do something you shouldn't" — neutral framing states the rule and the system's tolerance without judgement. |
| UX-3 | Title casing | **Sentence case everywhere except status pills and the Balanza save CTA** | Title case for buttons and headers; all-caps for headers | Title Case In Spanish Always Reads As Translated. All-caps creates emphasis only where it earns it: the save action and the binary pill states. |
| UX-4 | Save success microcopy | **"Pesaje guardado"** + animated check + `patente · neto · hora` line | "¡Listo!"; "Guardado con éxito"; just the animation | Two-word factual confirmation matches the system's voice. The check animation does the celebratory work; the words do the informational work. |
| UX-5 | Empty states | **Friendly, declarative, end with a period, no exclamation** (*"Sin pesajes en este turno todavía."*) | "¡No hay pesajes!"; cute illustrations + headline | The system narrates state without theatre. Periods over exclamations across the entire product. |
| UX-6 | Iconography for status | **Lucide `alert-triangle` + filled coloured dot, never emoji** | Use the spec's `⚠️` / `✅` / `⚫` directly | Emoji render inconsistently across OS versions and look unprofessional in an operational dashboard. Lucide + dots preserve the same semantic load without the variance. |
| UX-7 | Units | **Always shown, lowercase, after the value with a space** (`8.500 kg`, `142,5 t`, `1,3 kg/ha`) | Units in labels only ("Peso bruto en kg"); units suppressed when obvious | Operators triangulate numbers across screens (booth, dashboard, reports). Unit-in-value is the safe default — never ambiguous, never depends on reading context. |
| UX-8 | Time & dates | **24h time `14:32`; dates `dd/mm/yyyy`** | 12h with AM/PM; ISO `yyyy-mm-dd` | Standard for the Argentine government context. ISO survives only in machine-readable export filenames. |

### Pending decisions (parked)

These came up during ronda 1 and need a call before production. Listed so they're not forgotten.

- **§A · Renaming `kg/hectárea`** — the metric is ambiguous as a column label (kg/ha *of what*?). Pending a clearer name or a tooltip.
- **§B · "Pesajes" vs "Viajes"** — the dashboard uses both interchangeably. Pick one term and apply it everywhere (likely "Pesajes" for records, "Viajes" only when explicitly describing trips).
- **§C · Offline state** — header today always reads `En línea`. Needs an offline state with copy, behaviour for queued saves and a reconnection toast.
- **§D · Logout confirmation** — currently instant. Decide whether to confirm when an unsaved pesaje is in progress.
- **§E · Print/PDF layout for reports** — visual export buttons exist; the actual print stylesheet does not.

#### §F · Doble pesaje para el cálculo del neto *(open — was deferred at the egreso decision)*

The team chose **G.2** (egreso as timestamp + optional outgoing weight, for audit) but explicitly left open whether the system will eventually move to **G.3** — double weighing where the actual waste weight is computed as `bruto_entrada − bruto_salida` instead of `bruto_entrada − tara`. Today the `brutoSalida` field is captured and stored but is not used in any calculation.

Questions to resolve before this can be picked up:

1. **Operational truth** — does the actual scale workflow already involve a second weighing in practice (paper) that the digital system is ignoring? If yes, G.3 is closer to reality than G.2.
2. **Accuracy gap** — how often does the real outgoing weight differ from the stored `tara` by enough to matter (mud, retained cargo, missing parts)? If the gap is consistently small, the simpler G.2 stays. If it's material, G.3 wins.
3. **UX impact** — moving to G.3 makes the egress weighing *mandatory* and changes the wording (no more "opcional"), and the dashboard's `neto` for an open record becomes provisional until egreso. That's a real operator-training cost.
4. **Reporting impact** — historical pesajes use the tara-based neto. If we switch, do we re-compute history retroactively, or only forward?

Until those four answers exist, we leave G.2 in place and treat any `brutoSalida` captured today as audit-only data that's already on disk if the policy changes later.

- **§G · Accessibility audit** — colour contrast on orange and red surfaces hasn't been formally checked; no keyboard testing of the admin sidebar accordion; no screen-reader pass.

---

## Files index

| Path | What it is |
| --- | --- |
| `README.md` | This document. |
| `SKILL.md` | Front-matter file so this folder works as an Agent Skill in Claude Code. |
| `colors_and_type.css` | All design tokens as CSS custom properties + base semantic styles (`h1`, `p`, `.kpi-value`, etc). Import this first in any new HTML file. |
| `assets/logo-placeholder.svg` | Provisional wordmark — replace with the official Infinito Reciclaje mark when available. |
| `preview/` | The 18 preview cards rendered in the Design System tab (colours, type, spacing, components, brand). Each is a self-contained HTML demo of one sub-concept. |
| `ui_kits/balanza/` | The interactive UI kit. Open `index.html`. |

### UI kits

- **`ui_kits/balanza/`** — the only product surface, with eleven screens: Login, Balanza, Historial, Dashboard, Pesajes (admin), Vehículos, Orígenes, Tipos de servicio, Tipos de vehículo, Usuarios, Reportes. Demo credentials: `roberto / Evolvere123!@` (operator) · `nacho / Evolvere123!@` (admin). See its own `README.md` for the component vocabulary and per-file responsibilities.

---

## Quickstart

To build a new screen against this system:

1. Link `colors_and_type.css` and the Lucide CDN script.
2. Use the tokens (`var(--green-700)`, `var(--shadow-card)`, etc.) — never hex literals.
3. Pull components from `ui_kits/balanza/` as references; copy and adapt, don't import.
4. If the screen is operator-facing, use the Balanza layout. If admin-facing, use the admin shell with the left rail.
