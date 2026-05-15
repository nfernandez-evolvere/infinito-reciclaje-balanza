# UI kit · Sistema de Gestión de Balanza

Interactive click-through prototype for the operator + admin workflows. Single-page app with in-app navigation between screens.

## Screens

1. **Login** — `roberto / 1234` → Balanza · `nacho / 1234` → Dashboard
2. **Balanza (Pesaje)** — vertical sequential form: vehicle → service → weight → summary. 72 px weight readout, autocomplete, cascade *warning* (never override), keyboard shortcuts and a sticky action bar with shortcut hints.
3. **Historial** — last 20 weighings of the shift (operator view, read-only + egreso + edit)
4. **Dashboard (admin)** — alerts, KPIs, evolución diaria chart, zone + vehicle breakdowns
5. **Pesajes (admin)** — full filterable log + editable rows with mandatory motivo and immutable change log
6. **Zonas / Tipos de servicio / Vehículos / Tipos de vehículo / Usuarios** — five ABMs with full CRUD
7. **Reportes** — filterable preview with PDF / Excel export

Admin sidebar has two sections:
- **Flat items** (top): Operación (Dashboard · Pesajes · Reportes) · Padrón (Zonas · Tipos de servicio)
- **Accordion groups** (bottom): Transporte (Vehículos · Tipos de vehículo) · Sistema (Usuarios)

## Files

| File | Purpose |
| --- | --- |
| `index.html` | Entry point. Loads React 18, Babel, Lucide, Chart.js, the kit stylesheet and every JSX. |
| `styles.css` | Kit-only styles built on the root `colors_and_type.css` tokens. |
| `components.jsx` | Shared atoms: `Button`, `Field`, `Input`, `Pill`, `Card`, `Icon`, `Badge`, `KpiCard`, `Modal`, `Banner`, `SuccessOverlay`. |
| `data.jsx` | Static helpers and chart data. Entity data loaded from `data/*.json`. |
| `data/*.json` | JSON files for all mutable entities: `zonas`, `zona_servicios`, `vehiculos`, `tipos_vehiculo`, `servicios`, `usuarios`, `pesajes`, `pesajes_log`. |
| `AppContext.jsx` | React Context that loads all JSON via `fetch()` on mount, holds state and provides setters + derived values to all components. |
| `AppShell.jsx` | Top-level shell: operator header + footer; admin sidebar with labels and accordion groups. |
| `Login.jsx` | Login screen with visible demo credentials. |
| `Balanza.jsx` | Pesaje form — vertical layout, 72 px readout, keyboard shortcuts, cascade warning. Reads data from AppContext. |
| `Historial.jsx` | Operator's shift log — read-only + egreso + edit actions. |
| `Dashboard.jsx` | Admin home. |
| `PesajeModals.jsx` | Shared modals: `EditPesajeModal`, `EgresoModal`, `LogModal`. Reads servicios/zonas from AppContext. |
| `PesajesAdmin.jsx` | Full filterable pesaje list with editable rows + immutable change log per record. |
| `AbmVehiculos.jsx` | Vehículos ABM — full CRUD via AppContext. |
| `AbmZonas.jsx` | Zonas ABM — full CRUD via AppContext. Zone-service assignments with turnos switch and compact horario editor. |
| `AbmServicios.jsx` | Tipos de servicio ABM — full CRUD via AppContext. |
| `AbmTipos.jsx` | Tipos de vehículo ABM — full CRUD via AppContext. Ranges = peso bruto (gross weight). |
| `AbmUsuarios.jsx` | Usuarios ABM — full CRUD via AppContext. |
| `Reportes.jsx` | Reporte preview screen. |

## Dependencies

Loaded from CDN, no bundler:

- React 18 (development build)
- Babel standalone 7.29.0
- Lucide icons (latest)
- Chart.js 4

## Data flow

Initial state loaded from `data/*.json` via `fetch()` in `AppContext`. All edits live in React Context (in-memory). A page refresh reloads from the JSON files — edits are not persisted.

ABM components read and write directly to AppContext. Balanza, PesajeModals, PesajesAdmin and Reportes receive derived values (servicioNames, zonaNames, vehicleTypeMap, servicioCascade) from the same context.

## Notes

- **Not production code.** No API calls — all persistence is in-memory via React Context.
- Every numeric column uses `.num` (`font-variant-numeric: tabular-nums`) — never break this when extending.
- Confirmation modals are required for all destructive actions (deactivate, remove service assignment).
- Zona turnos are configured per zona+servicio combination, not globally per service.
