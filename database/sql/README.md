# Scripts SQL — Infinito Reciclaje Balanza

## Mecanismo de trabajo

Las tablas **no se crean con migraciones automáticas de Laravel**. Se administran con scripts T-SQL que se ejecutan manualmente sobre SQL Server.

## Convención de nombres

```
[BaseDatos].[infinito_balanza].[{ambiente}_{tabla}]
```

| Parte | Descripción |
|-------|-------------|
| `BaseDatos` | Base de datos compartida de la empresa (configurar en `.env`) |
| `infinito_balanza` | Schema fijo del proyecto |
| `{ambiente}` | Prefijo de ambiente: `dev_`, `stg_`, `prod_` |
| `{tabla}` | Nombre de la tabla en snake_case |

**Ejemplo:** `[MiDB].[infinito_balanza].[dev_vehiculos]`

## Estructura de carpetas

```
database/sql/
├── README.md          ← este archivo
├── dev/               ← ambiente de desarrollo
│   ├── 001_laravel_tables.sql    ← tablas internas del framework
│   └── 002_domain_tables.sql    ← tablas de dominio del proyecto
├── stg/               ← staging (mismo contenido, prefijo stg_)
└── prod/              ← producción (mismo contenido, prefijo prod_)
```

## Cómo ejecutar

1. Conectarse al servidor SQL Server con permisos de DDL sobre la base de datos.
2. Ejecutar los scripts en orden numérico dentro del ambiente correspondiente.
3. Todos los scripts son **idempotentes** (`IF NOT EXISTS`) — se pueden re-ejecutar sin error.

## Cómo agregar una tabla nueva

1. Crear un script numerado en cada ambiente (`003_nueva_tabla.sql`).
2. Copiar el archivo a `stg/` y `prod/` cambiando el prefijo de `dev_` a `stg_` o `prod_`.
3. Documentar la tabla en `docs/03-data-model.md`.

## Configuración en Laravel

Laravel usa el prefijo de ambiente leyéndolo de `.env`:

```env
DB_CONNECTION=sqlsrv
DB_HOST=
DB_PORT=1433
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
DB_TABLE_PREFIX=dev_
```

El schema `infinito_balanza` se resuelve configurando el **default schema del usuario SQL Server**:

```sql
ALTER USER [nombre_usuario] WITH DEFAULT_SCHEMA = infinito_balanza;
```

De esta forma Laravel no necesita conocer el schema — genera `SELECT * FROM [dev_tabla]`
y SQL Server lo resuelve a `[infinito_balanza].[dev_tabla]` automáticamente.

## Cambiar de ambiente

Solo cambia `DB_TABLE_PREFIX` en `.env`:

| Ambiente | Valor |
|----------|-------|
| Desarrollo | `DB_TABLE_PREFIX=dev_` |
| Staging | `DB_TABLE_PREFIX=stg_` |
| Producción | `DB_TABLE_PREFIX=prod_` |
