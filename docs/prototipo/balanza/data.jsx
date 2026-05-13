/* global React */
// Hardcoded master data for the prototype.

const VEHICLE_TYPES = {
  Compactador: { rangeMin: 10000, rangeMax: 26500 },
  Volcador:    { rangeMin: 13000, rangeMax: 30000 },
  Volquete:    { rangeMin: 7000,  rangeMax: 20000 },
  Particular:  { rangeMin: 1000,  rangeMax: 5000  },
};

const VEHICLES = [
  { id: 1, patente: "ABC-123", interno: "45", tipo: "Compactador", tara: 8500,  titular: "Municipal",  estado: "Activo" },
  { id: 2, patente: "DEF-456", interno: "12", tipo: "Volcador",    tara: 12000, titular: "Municipal",  estado: "Activo" },
  { id: 3, patente: "GHI-789", interno: "23", tipo: "Compactador", tara: 9000,  titular: "Particular", estado: "Activo" },
  { id: 4, patente: "JKL-012", interno: "67", tipo: "Volquete",    tara: 6500,  titular: "Municipal",  estado: "Activo" },
  { id: 5, patente: "MNO-345", interno: "89", tipo: "Volcador",    tara: 11500, titular: "Municipal",  estado: "Inactivo" },
  { id: 6, patente: "PQR-678", interno: "34", tipo: "Compactador", tara: 8800,  titular: "Municipal",  estado: "Activo" },
];

const SERVICIOS_DATA = [
  { id: 1, nombre: "Domiciliario",             turnos: ["Diurna", "Nocturna"], tipoSugerido: "Compactador", estado: "Activo" },
  { id: 2, nombre: "Voluminoso",               turnos: [],                     tipoSugerido: "Volquete",    estado: "Activo" },
  { id: 3, nombre: "Barrido",                  turnos: [],                     tipoSugerido: "Volcador",    estado: "Activo" },
  { id: 4, nombre: "Servicios Especiales",     turnos: [],                     tipoSugerido: "Compactador", estado: "Activo" },
  { id: 5, nombre: "Centros de Transferencia", turnos: [],                     tipoSugerido: "Compactador", estado: "Activo" },
];
const SERVICIOS = SERVICIOS_DATA.map((s) => s.nombre);
const SERVICIO_CASCADE = Object.fromEntries(
  SERVICIOS_DATA.map((s) => [s.nombre, { tipoSugerido: s.tipoSugerido, turnos: s.turnos }])
);

const ZONAS_DATA = [
  { id: 1, nombre: "Centro",  tipoServicio: "Domiciliario",             hectareas: 50, barrios: 12, estado: "Activo" },
  { id: 2, nombre: "Norte",   tipoServicio: "Barrido",                  hectareas: 42, barrios: 9,  estado: "Activo" },
  { id: 3, nombre: "Sur",     tipoServicio: "Domiciliario",             hectareas: 55, barrios: 14, estado: "Activo" },
  { id: 4, nombre: "Oeste",   tipoServicio: "Voluminoso",               hectareas: 38, barrios: 8,  estado: "Activo" },
  { id: 5, nombre: "Este",    tipoServicio: "Servicios Especiales",     hectareas: 31, barrios: 7,  estado: "Activo" },
  { id: 6, nombre: "Puerto",  tipoServicio: "Centros de Transferencia", hectareas: 18, barrios: 2,  estado: "Activo" },
];
const ZONAS = ZONAS_DATA.map((z) => z.nombre);

const USUARIOS = [
  { id: 1, usuario: "roberto",  nombre: "Roberto Acosta",   rol: "Operador", estado: "Activo" },
  { id: 2, usuario: "marta",    nombre: "Marta Giménez",    rol: "Operador", estado: "Activo" },
  { id: 3, usuario: "nacho",    nombre: "Nacho Ríos",       rol: "Admin",    estado: "Activo" },
  { id: 4, usuario: "carlos",   nombre: "Carlos Vera",      rol: "Operador", estado: "Inactivo" },
];

const PESAJES = [
  { id: 101, horaEntrada: "14:32", horaSalida: null,    patente: "ABC-123", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 24350, tara: 8500,  brutoSalida: null,  neto: 15850, operador: "roberto", estado: "En predio" },
  { id: 102, horaEntrada: "14:18", horaSalida: null,    patente: "DEF-456", tipo: "Volcador",    servicio: "Barrido",                   zona: "Norte",  bruto: 27800, tara: 12000, brutoSalida: null,  neto: 15800, operador: "roberto", estado: "En predio" },
  { id: 103, horaEntrada: "14:02", horaSalida: "14:24", patente: "GHI-789", tipo: "Compactador", servicio: "Domiciliario",              zona: "Sur",    bruto: 22100, tara: 9000,  brutoSalida: 9120, neto: 13100, operador: "roberto", estado: "Cerrado" },
  { id: 104, horaEntrada: "13:47", horaSalida: "14:11", patente: "JKL-012", tipo: "Volquete",    servicio: "Voluminoso",                zona: "Oeste",  bruto: 18500, tara: 6500,  brutoSalida: 6580, neto: 12000, operador: "roberto", estado: "Cerrado" },
  { id: 105, horaEntrada: "13:31", horaSalida: "13:52", patente: "ABC-123", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 23900, tara: 8500,  brutoSalida: 8540, neto: 15400, operador: "roberto", estado: "Cerrado" },
  { id: 106, horaEntrada: "13:14", horaSalida: "13:36", patente: "PQR-678", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 25100, tara: 8800,  brutoSalida: 8830, neto: 16300, operador: "roberto", estado: "Cerrado" },
  { id: 107, horaEntrada: "12:58", horaSalida: "13:18", patente: "DEF-456", tipo: "Volcador",    servicio: "Barrido",                   zona: "Norte",  bruto: 26400, tara: 12000, brutoSalida: 12040, neto: 14400, operador: "roberto", estado: "Cerrado" },
  { id: 108, horaEntrada: "12:47", horaSalida: "13:09", patente: "GHI-789", tipo: "Compactador", servicio: "Domiciliario",              zona: "Sur",    bruto: 21800, tara: 9000,  brutoSalida: 9050, neto: 12800, operador: "roberto", estado: "Cerrado" },
  { id: 109, horaEntrada: "12:15", horaSalida: "12:38", patente: "JKL-012", tipo: "Volquete",    servicio: "Voluminoso",                zona: "Oeste",  bruto: 19200, tara: 6500,  brutoSalida: 6520, neto: 12700, operador: "roberto", estado: "Cerrado" },
  { id: 110, horaEntrada: "11:58", horaSalida: "12:22", patente: "ABC-123", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 24800, tara: 8500,  brutoSalida: 8550, neto: 16300, operador: "roberto", estado: "Cerrado" },
  { id: 111, horaEntrada: "11:41", horaSalida: "12:04", patente: "PQR-678", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 23700, tara: 8800,  brutoSalida: 8810, neto: 14900, operador: "roberto", estado: "Cerrado" },
  { id: 112, horaEntrada: "11:22", horaSalida: "11:48", patente: "DEF-456", tipo: "Volcador",    servicio: "Barrido",                   zona: "Norte",  bruto: 28100, tara: 12000, brutoSalida: 12080, neto: 16100, operador: "marta",   estado: "Cerrado", editado: true },
  { id: 113, horaEntrada: "11:04", horaSalida: "11:28", patente: "GHI-789", tipo: "Compactador", servicio: "Domiciliario",              zona: "Sur",    bruto: 22600, tara: 9000,  brutoSalida: 9080, neto: 13600, operador: "marta",   estado: "Cerrado" },
  { id: 114, horaEntrada: "10:48", horaSalida: "11:14", patente: "JKL-012", tipo: "Volquete",    servicio: "Voluminoso",                zona: "Oeste",  bruto: 18900, tara: 6500,  brutoSalida: 6510, neto: 12400, operador: "marta",   estado: "Cerrado" },
  { id: 115, horaEntrada: "10:33", horaSalida: "10:57", patente: "ABC-123", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 24200, tara: 8500,  brutoSalida: 8540, neto: 15700, operador: "marta",   estado: "Cerrado" },
  { id: 116, horaEntrada: "10:11", horaSalida: "10:35", patente: "DEF-456", tipo: "Volcador",    servicio: "Servicios Especiales",      zona: "Sur",    bruto: 24500, tara: 12000, brutoSalida: 12050, neto: 12500, operador: "marta",   estado: "Cerrado" },
  { id: 117, horaEntrada: "09:54", horaSalida: "10:21", patente: "PQR-678", tipo: "Compactador", servicio: "Domiciliario",              zona: "Centro", bruto: 25600, tara: 8800,  brutoSalida: 8820, neto: 16800, operador: "marta",   estado: "Cerrado" },
  { id: 118, horaEntrada: "09:31", horaSalida: "09:58", patente: "GHI-789", tipo: "Compactador", servicio: "Centros de Transferencia",  zona: "Centro", bruto: 23200, tara: 9000,  brutoSalida: 9100, neto: 14200, operador: "marta",   estado: "Cerrado" },
];

const PESAJES_LOG = [
  { id: 1, pesajeId: 112, fecha: "2026-03-12 11:22", usuario: "nacho", campo: "bruto", anterior: 8100, nuevo: 28100, motivo: "Tipeo: faltaba el 2 inicial." },
];

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
  { zona: "Centro", pesajes: 18, t: 65.0, kgHa: 1.3 },
  { zona: "Sur",    pesajes: 14, t: 48.2, kgHa: 0.9 },
  { zona: "Norte",  pesajes: 10, t: 29.3, kgHa: 0.7 },
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

Object.assign(window, {
  VEHICLE_TYPES, VEHICLES,
  SERVICIOS, SERVICIOS_DATA, SERVICIO_CASCADE,
  ZONAS, ZONAS_DATA, USUARIOS,
  PESAJES, PESAJES_LOG, ALERTS, DAILY_EVOLUTION, ZONE_BREAKDOWN, TYPE_BREAKDOWN,
  fmtKg, fmtN, fmtT, fmtPct,
});
