/* global React */
// Datos estáticos y helpers compartidos. Las entidades mutables viven en AppContext (cargadas desde data/*.json).

const ALERTS = [
  { kind: "warn", title: "Gap en registros 12:30 — 12:45", body: "15 minutos sin pesajes durante turno activo." },
  { kind: "warn", title: "Peso inusual ABC-123: 32.000 kg", body: "Por encima del rango habitual para Compactador (10.000 – 26.500 kg)." },
];

const DAILY_EVOLUTION = [
  { day: "Lun", t: 138 }, { day: "Mar", t: 145 }, { day: "Mié", t: 132 },
  { day: "Jue", t: 148 }, { day: "Vie", t: 141 }, { day: "Sáb", t: 89 },
  { day: "Hoy", t: 143 },
];

const ZONE_BREAKDOWN = [
  { origen: "Centro", pesajes: 18, t: 65.0, kgHa: 1.3 },
  { origen: "Sur",    pesajes: 14, t: 48.2, kgHa: 0.9 },
  { origen: "Norte",  pesajes: 10, t: 29.3, kgHa: 0.7 },
];

const TYPE_BREAKDOWN = [
  { tipo: "Compactador", viajes: 28, t: 98.2, pct: 68.9 },
  { tipo: "Volcador",    viajes: 10, t: 33.1, pct: 23.2 },
  { tipo: "Volquete",    viajes: 4,  t: 11.2, pct: 7.9  },
];

const fmtKg  = (n) => Math.round(n).toLocaleString("es-AR") + " kg";
const fmtN   = (n) => Math.round(n).toLocaleString("es-AR");
const fmtT   = (n, d = 1) => n.toLocaleString("es-AR", { minimumFractionDigits: d, maximumFractionDigits: d }) + " t";
const fmtPct = (n) => n.toLocaleString("es-AR", { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + "%";

// Genera entradas de log por cada campo modificado en un pesaje
function buildLogEntries({ pesajeId, patch, original, actor, motivo }) {
  const fecha = new Date().toLocaleString("es-AR", { hour12: false }).replace(",", "");
  const campos = ["bruto", "tara", "servicio", "origen"];
  return campos
    .filter((c) => patch[c] !== undefined && patch[c] !== original[c])
    .map((c, i) => ({
      id: `${Date.now()}-${i}`,
      pesajeId, fecha, usuario: actor,
      campo: c, anterior: original[c], nuevo: patch[c], motivo,
    }));
}

Object.assign(window, {
  ALERTS, DAILY_EVOLUTION, ZONE_BREAKDOWN, TYPE_BREAKDOWN,
  fmtKg, fmtN, fmtT, fmtPct, buildLogEntries,
});
