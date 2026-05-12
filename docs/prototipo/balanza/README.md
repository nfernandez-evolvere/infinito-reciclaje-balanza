# UI kit · Sistema de Gestión de Balanza

Interactive click-through prototype for the operator + admin workflows. Single-page app with in-app navigation between six screens.

## Screens

1. **Login** — `roberto / 1234` → Balanza · `nacho / 1234` → Dashboard
2. **Balanza (Pesaje)** — vertical sequential form: vehicle → service → weight → summary. 72 px weight readout, autocomplete, cascade *warning* (never override), keyboard shortcuts and a sticky action bar with shortcut hints.
3. **Historial** — last 20 weighings of the shift (operator view, read-only)
4. **Dashboard (admin)** — alerts, KPIs, evolución diaria chart, zone + vehicle breakdowns
5. **Pesajes (admin)** — full filterable log + editable rows with mandatory motivo and immutable change log
6. **Vehículos / Zonas / Tipos de servicio / Tipos de vehículo / Usuarios** — five ABMs
7. **Reportes** — filterable preview with PDF / Excel export

Admin navigation is grouped as an **accordion** sidebar: *Operación* · *Padrones* · *Análisis*.

## Files

| File | Purpose |
| --- | --- |
| `index.html` | Entry point. Loads React 18, Babel, Lucide, Chart.js, the kit stylesheet and every JSX. |
| `styles.css` | Kit-only styles built on the root `colors_and_type.css` tokens. |
| `components.jsx` | Shared atoms: `Button`, `Field`, `Input`, `Pill`, `Card`, `Icon`, `Badge`, `KpiCard`, `Modal`, `Banner`, `SuccessOverlay`. |
| `data.jsx` | Hardcoded master data: vehicles, vehicle types + ranges, services, zones, users, sample weighings + change log. |
| `AppShell.jsx` | Top-level shell: operator header + footer; admin accordion sidebar. |
| `Login.jsx` | Login screen with visible demo credentials. |
| `Balanza.jsx` | Pesaje form — vertical layout, 72 px readout, keyboard shortcuts, cascade warning. |
| `Historial.jsx` | Operator's read-only shift log. |
| `Dashboard.jsx` | Admin home. |
| `PesajesAdmin.jsx` | Full filterable pesaje list with editable rows + immutable change log per record. |
| `AbmVehiculos.jsx` · `AbmZonas.jsx` · `AbmServicios.jsx` · `AbmTipos.jsx` · `AbmUsuarios.jsx` | The five padrones. |
| `Reportes.jsx` | Reporte preview screen. |

## Dependencies

Loaded from CDN, no bundler:

- React 18 (development build)
- Babel standalone 7.29.0
- Lucide icons (latest)
- Chart.js 4

## Notes

- **Not production code.** Persistence is in-memory; "saving" updates the local state, no API calls.
- Components don't share their `styles` constants — each file uses an inline-style approach or namespaced constants to avoid global collisions per the React-from-Babel rules.
- Every numeric column uses `.num` (`font-variant-numeric: tabular-nums`) — never break this when extending.
