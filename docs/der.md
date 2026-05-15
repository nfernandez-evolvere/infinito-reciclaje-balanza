# DER — Sistema de Gestión de Balanza
## Infinito Reciclaje × EVOLVERE 2026

**Motor:** SQL Server · **ORM:** Laravel Eloquent · **Versión:** 1.1

---

```mermaid
erDiagram

    users {
        bigint      id              PK
        nvarchar    name
        nvarchar    email           UK
        nvarchar    role            "operador | admin"
        bit         activo
        bit         onboarding_visto
    }

    tipos_vehiculo {
        bigint      id              PK
        nvarchar    nombre          UK
        int         peso_min_kg     "peso bruto mínimo"
        int         peso_max_kg     "peso bruto máximo"
        bit         activo
    }

    tipos_servicio {
        bigint      id              PK
        nvarchar    nombre          UK
        bigint      tipo_vehiculo_sugerido_id FK
        bit         activo
    }

    zonas {
        bigint      id              PK
        nvarchar    nombre          UK
        decimal     hectareas       "nullable"
        int         barrios         "nullable"
        int         habitantes      "nullable"
        bit         activo
    }

    zona_servicios {
        bigint      zona_id         PK
        bigint      tipo_servicio_id PK
    }

    zona_servicio_turnos {
        bigint      zona_id         PK
        bigint      tipo_servicio_id PK
        nvarchar    turno           PK "Diurna | Nocturna"
    }

    zona_servicio_horarios {
        bigint      zona_id         PK
        bigint      tipo_servicio_id PK
        tinyint     dia_semana      PK "1=Lun … 7=Dom"
        tinyint     franja          PK "nro de franja del día"
        time        hora_inicio
        time        hora_fin        "puede cruzar medianoche"
    }

    vehiculos {
        bigint      id              PK
        nvarchar    patente         UK
        nvarchar    numero_interno  UK
        int         tara_kg         "copiado al pesaje en ingreso"
        bigint      tipo_vehiculo_id FK
        nvarchar    titular
        bit         activo
    }

    pesajes {
        bigint      id              PK
        bigint      vehiculo_id     FK
        bigint      operador_id     FK
        bigint      tipo_servicio_id FK
        bigint      zona_id         FK
        nvarchar    turno           "nullable"
        int         peso_bruto_kg
        int         peso_tara_kg    "snapshot de tara al ingreso"
        int         peso_neto_kg    "bruto - tara"
        bit         alerta_peso
        nvarchar    estado          "En predio | Cerrado"
        bit         editado
        datetime2   hora_salida     "nullable"
        int         bruto_salida_kg "nullable"
    }

    pesajes_log {
        bigint      id              PK
        bigint      pesaje_id       FK
        nvarchar    campo
        nvarchar    valor_anterior  "nullable"
        nvarchar    valor_nuevo     "nullable"
        nvarchar    motivo          "obligatorio"
        bigint      usuario_id      FK
        datetime2   created_at
    }

    alarmas {
        bigint      id              PK
        nvarchar    tipo            "gap_pesajes | peso_inusual | frecuencia_atipica"
        nvarchar    descripcion
        bigint      zona_id         FK "nullable"
        bigint      vehiculo_id     FK "nullable"
        bigint      pesaje_id       FK "nullable"
        bit         resuelta
        bigint      resuelta_por    FK "nullable"
        datetime2   resuelta_at     "nullable"
    }

    config_alarmas {
        bigint      id              PK
        nvarchar    tipo            UK
        int         umbral_valor
        nvarchar    descripcion_umbral
        bit         activo
    }

    %% ── Padrón de vehículos ──────────────────────────────────
    tipos_vehiculo      ||--o{  vehiculos           : "tipo_vehiculo_id"
    tipos_vehiculo      |o--o{  tipos_servicio       : "tipo_vehiculo_sugerido_id"

    %% ── Configuración de zonas ───────────────────────────────
    zonas               ||--o{  zona_servicios       : "zona_id"
    tipos_servicio      ||--o{  zona_servicios       : "tipo_servicio_id"
    zona_servicios      ||--o{  zona_servicio_turnos : "zona_id + tipo_servicio_id"
    zona_servicios      ||--o{  zona_servicio_horarios : "zona_id + tipo_servicio_id"

    %% ── Pesajes ──────────────────────────────────────────────
    vehiculos           ||--o{  pesajes              : "vehiculo_id"
    users               ||--o{  pesajes              : "operador_id"
    tipos_servicio      ||--o{  pesajes              : "tipo_servicio_id"
    zonas               ||--o{  pesajes              : "zona_id"

    %% ── Auditoría ────────────────────────────────────────────
    pesajes             ||--o{  pesajes_log          : "pesaje_id"
    users               ||--o{  pesajes_log          : "usuario_id"

    %% ── Alarmas ──────────────────────────────────────────────
    zonas               |o--o{  alarmas              : "zona_id"
    vehiculos           |o--o{  alarmas              : "vehiculo_id"
    pesajes             |o--o|  alarmas              : "pesaje_id"
    users               |o--o{  alarmas              : "resuelta_por"
```

---

## Cardinalidades

| Relación | Tipo | Notas |
|----------|------|-------|
| `tipos_vehiculo` → `vehiculos` | 1:N | Un tipo tiene muchos vehículos. RESTRICT en delete. |
| `tipos_vehiculo` → `tipos_servicio` | 1:0..N | Sugerencia nullable. SET NULL en delete. |
| `zonas` ↔ `tipos_servicio` (vía `zona_servicios`) | N:M | Una zona opera bajo varios servicios; un servicio opera en varias zonas. |
| `zona_servicios` → `zona_servicio_turnos` | 1:0..N | 0 = sin turno; 1 = solo Diurna o solo Nocturna; 2 = ambos. |
| `zona_servicios` → `zona_servicio_horarios` | 1:0..N | Múltiples franjas por día, optativo. |
| `vehiculos` → `pesajes` | 1:N | RESTRICT en delete. |
| `users` → `pesajes` | 1:N | Operador que registra. RESTRICT en delete. |
| `tipos_servicio` → `pesajes` | 1:N | RESTRICT en delete. |
| `zonas` → `pesajes` | 1:N | RESTRICT en delete. |
| `pesajes` → `pesajes_log` | 1:N | Append-only. RESTRICT en delete. |
| `users` → `pesajes_log` | 1:N | Usuario que editó. RESTRICT en delete. |
| `zonas` → `alarmas` | 1:0..N | Nullable. SET NULL en delete. |
| `vehiculos` → `alarmas` | 1:0..N | Nullable. SET NULL en delete. |
| `pesajes` → `alarmas` | 1:0..1 | Un pesaje genera a lo sumo una alarma de peso inusual. |
| `users` → `alarmas` | 1:0..N | Admin que resolvió. Nullable. RESTRICT en delete. |

---

## Grupos funcionales

```
┌─ PADRÓN MAESTRO ──────────────────────────────────────────┐
│  tipos_vehiculo  ←─  vehiculos                            │
│  tipos_vehiculo  ←─  tipos_servicio                       │
│  zonas  ──────────── zona_servicios ─── zona_servicio_turnos
│                                    └─── zona_servicio_horarios
└────────────────────────────────────────────────────────────┘

┌─ OPERACIÓN ────────────────────────────────────────────────┐
│  pesajes  (vehiculo + servicio + zona + operador + pesos)  │
│  pesajes_log  (audit trail inmutable)                      │
└────────────────────────────────────────────────────────────┘

┌─ ALERTAS ──────────────────────────────────────────────────┐
│  alarmas  (zona | vehiculo | pesaje)                       │
│  config_alarmas  (umbrales y toggles)                      │
└────────────────────────────────────────────────────────────┘

┌─ ACCESO ───────────────────────────────────────────────────┐
│  users  (operadores y admins)                              │
└────────────────────────────────────────────────────────────┘
```

---

*Diagrama generado: 14/05/2026 | Referencia completa: [`data-model.md`](data-model.md)*
