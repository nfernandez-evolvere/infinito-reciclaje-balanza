-- =====================================================================
-- INFINITO RECICLAJE — BALANZA
-- Ambiente : DEV
-- Schema   : infinito_balanza
-- Prefijo  : dev_
-- Script   : 002 — Tablas de dominio del proyecto (multi-tenant)
-- =====================================================================
-- Orden de creación respeta dependencias (FK).
-- Ejecutar después de 001_laravel_tables.sql.
-- =====================================================================

-- ---------------------------------------------------------------------
-- dev_organizaciones  (tenant raíz)
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_organizaciones]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_organizaciones] (
    [id]         BIGINT IDENTITY(1,1) NOT NULL,
    [nombre]     NVARCHAR(150)        NOT NULL,
    [slug]       NVARCHAR(100)        NOT NULL,
    [activo]     BIT                  NOT NULL DEFAULT 1,
    [created_at] DATETIME2(0)         NULL,
    [updated_at] DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_organizaciones]        PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_organizaciones_slug]   UNIQUE ([slug])
);
GO

-- ---------------------------------------------------------------------
-- dev_users
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_users]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_users] (
    [id]                BIGINT IDENTITY(1,1) NOT NULL,
    [organizacion_id]   BIGINT               NULL,
    [name]              NVARCHAR(255)        NOT NULL,
    [email]             NVARCHAR(255)        NOT NULL,
    [email_verified_at] DATETIME2(0)         NULL,
    [password]          NVARCHAR(255)        NOT NULL,
    [role]              NVARCHAR(20)         NOT NULL DEFAULT 'operador',
    [onboarding_visto]  BIT                  NOT NULL DEFAULT 0,
    [activo]            BIT                  NOT NULL DEFAULT 1,
    [remember_token]    NVARCHAR(100)        NULL,
    [created_at]        DATETIME2(0)         NULL,
    [updated_at]        DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_users]              PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_users_email_org]    UNIQUE ([organizacion_id], [email]),
    CONSTRAINT [CK_dev_users_role]         CHECK ([role] IN ('super_admin', 'admin', 'operador')),
    -- super_admin no pertenece a ninguna org; admin/operador siempre pertenecen a una
    CONSTRAINT [CK_dev_users_org_role]     CHECK (
        ([role] = 'super_admin' AND [organizacion_id] IS NULL)
        OR ([role] != 'super_admin' AND [organizacion_id] IS NOT NULL)
    ),
    CONSTRAINT [FK_dev_users_organizacion] FOREIGN KEY ([organizacion_id])
        REFERENCES [infinito_balanza].[dev_organizaciones] ([id]) ON DELETE SET NULL
);
GO

-- ---------------------------------------------------------------------
-- dev_password_reset_tokens
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_password_reset_tokens]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_password_reset_tokens] (
    [email]      NVARCHAR(255) NOT NULL,
    [token]      NVARCHAR(255) NOT NULL,
    [created_at] DATETIME2(0)  NULL,
    CONSTRAINT [PK_dev_password_reset_tokens] PRIMARY KEY ([email])
);
GO

-- ---------------------------------------------------------------------
-- dev_tipos_vehiculo
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_tipos_vehiculo]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_tipos_vehiculo] (
    [id]               BIGINT IDENTITY(1,1) NOT NULL,
    [organizacion_id]  BIGINT               NOT NULL,
    [nombre]           NVARCHAR(255)        NOT NULL,
    [peso_min_kg]      INT                  NOT NULL,
    [peso_max_kg]      INT                  NOT NULL,
    [activo]           BIT                  NOT NULL DEFAULT 1,
    [created_at]       DATETIME2(0)         NULL,
    [updated_at]       DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_tipos_vehiculo]              PRIMARY KEY ([id]),
    CONSTRAINT [CK_dev_tipos_vehiculo_peso_min]     CHECK ([peso_min_kg] >= 0),
    CONSTRAINT [CK_dev_tipos_vehiculo_peso_max]     CHECK ([peso_max_kg] >= 0),
    CONSTRAINT [CK_dev_tipos_vehiculo_peso_rango]   CHECK ([peso_max_kg] >= [peso_min_kg]),
    CONSTRAINT [FK_dev_tipos_vehiculo_organizacion] FOREIGN KEY ([organizacion_id])
        REFERENCES [infinito_balanza].[dev_organizaciones] ([id]) ON DELETE CASCADE
);
GO

-- ---------------------------------------------------------------------
-- dev_tipos_servicio
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_tipos_servicio]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_tipos_servicio] (
    [id]                        BIGINT IDENTITY(1,1) NOT NULL,
    [organizacion_id]           BIGINT               NOT NULL,
    [nombre]                    NVARCHAR(100)        NOT NULL,
    [tipo_vehiculo_sugerido_id] BIGINT               NULL,
    [activo]                    BIT                  NOT NULL DEFAULT 1,
    [created_at]                DATETIME2(0)         NULL,
    [updated_at]                DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_tipos_servicio]               PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_tipos_servicio_nombre_org]    UNIQUE ([organizacion_id], [nombre]),
    CONSTRAINT [FK_dev_tipos_servicio_organizacion]  FOREIGN KEY ([organizacion_id])
        REFERENCES [infinito_balanza].[dev_organizaciones] ([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_dev_tipos_servicio_tipo_vehiculo] FOREIGN KEY ([tipo_vehiculo_sugerido_id])
        REFERENCES [infinito_balanza].[dev_tipos_vehiculo] ([id]) ON DELETE NO ACTION
);
GO

-- ---------------------------------------------------------------------
-- dev_vehiculos
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_vehiculos]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_vehiculos] (
    [id]               BIGINT IDENTITY(1,1) NOT NULL,
    [organizacion_id]  BIGINT               NOT NULL,
    [patente]          NVARCHAR(20)         NOT NULL,
    [numero_interno]   NVARCHAR(20)         NOT NULL,
    [tara_kg]          INT                  NOT NULL,
    [tipo_vehiculo_id] BIGINT               NOT NULL,
    [titular]          NVARCHAR(200)        NOT NULL,
    [capacidad_kg]     INT                  NULL,
    [observaciones]    NVARCHAR(500)        NULL,
    [activo]           BIT                  NOT NULL DEFAULT 1,
    [created_at]       DATETIME2(0)         NULL,
    [updated_at]       DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_vehiculos]                    PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_vehiculos_patente_org]        UNIQUE ([organizacion_id], [patente]),
    CONSTRAINT [UQ_dev_vehiculos_numero_interno_org] UNIQUE ([organizacion_id], [numero_interno]),
    CONSTRAINT [FK_dev_vehiculos_organizacion]       FOREIGN KEY ([organizacion_id])
        REFERENCES [infinito_balanza].[dev_organizaciones] ([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_dev_vehiculos_tipo_vehiculo]      FOREIGN KEY ([tipo_vehiculo_id])
        REFERENCES [infinito_balanza].[dev_tipos_vehiculo] ([id]) ON DELETE NO ACTION
);
GO

-- ---------------------------------------------------------------------
-- dev_zonas
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_zonas]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_zonas] (
    [id]               BIGINT IDENTITY(1,1) NOT NULL,
    [organizacion_id]  BIGINT               NOT NULL,
    [nombre]           NVARCHAR(150)        NOT NULL,
    [hectareas]        DECIMAL(10, 2)       NULL,
    [barrios]          INT                  NULL,
    [habitantes]       INT                  NULL,
    [activo]           BIT                  NOT NULL DEFAULT 1,
    [created_at]       DATETIME2(0)         NULL,
    [updated_at]       DATETIME2(0)         NULL,
    CONSTRAINT [PK_dev_zonas]                  PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_zonas_nombre_org]       UNIQUE ([organizacion_id], [nombre]),
    CONSTRAINT [FK_dev_zonas_organizacion]     FOREIGN KEY ([organizacion_id])
        REFERENCES [infinito_balanza].[dev_organizaciones] ([id]) ON DELETE CASCADE
);
GO

-- ---------------------------------------------------------------------
-- dev_zona_servicios  (pivote zona ↔ tipo_servicio)
-- El tenant viaja transitivamente desde zonas y tipos_servicio.
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_zona_servicios]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_zona_servicios] (
    [zona_id]          BIGINT       NOT NULL,
    [tipo_servicio_id] BIGINT       NOT NULL,
    [created_at]       DATETIME2(0) NULL,
    [updated_at]       DATETIME2(0) NULL,
    CONSTRAINT [PK_dev_zona_servicios]                   PRIMARY KEY ([zona_id], [tipo_servicio_id]),
    CONSTRAINT [FK_dev_zona_servicios_zona]              FOREIGN KEY ([zona_id])
        REFERENCES [infinito_balanza].[dev_zonas] ([id]) ON DELETE CASCADE,
    CONSTRAINT [FK_dev_zona_servicios_tipo_servicio]     FOREIGN KEY ([tipo_servicio_id])
        REFERENCES [infinito_balanza].[dev_tipos_servicio] ([id]) ON DELETE NO ACTION
);
GO

-- ---------------------------------------------------------------------
-- dev_zona_servicio_turnos
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_zona_servicio_turnos]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_zona_servicio_turnos] (
    [zona_id]          BIGINT       NOT NULL,
    [tipo_servicio_id] BIGINT       NOT NULL,
    [turno]            NVARCHAR(10) NOT NULL,
    CONSTRAINT [PK_dev_zona_servicio_turnos]                    PRIMARY KEY ([zona_id], [tipo_servicio_id], [turno]),
    CONSTRAINT [FK_dev_zona_servicio_turnos_zona_servicio]      FOREIGN KEY ([zona_id], [tipo_servicio_id])
        REFERENCES [infinito_balanza].[dev_zona_servicios] ([zona_id], [tipo_servicio_id]) ON DELETE CASCADE
);
GO

-- ---------------------------------------------------------------------
-- dev_zona_servicio_horarios
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_zona_servicio_horarios]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_zona_servicio_horarios] (
    [zona_id]          BIGINT   NOT NULL,
    [tipo_servicio_id] BIGINT   NOT NULL,
    [dia_semana]       TINYINT  NOT NULL,
    [franja]           TINYINT  NOT NULL,
    [hora_inicio]      TIME(0)  NOT NULL,
    [hora_fin]         TIME(0)  NOT NULL,
    CONSTRAINT [PK_dev_zona_servicio_horarios]                   PRIMARY KEY ([zona_id], [tipo_servicio_id], [dia_semana], [franja]),
    CONSTRAINT [FK_dev_zona_servicio_horarios_zona_servicio]     FOREIGN KEY ([zona_id], [tipo_servicio_id])
        REFERENCES [infinito_balanza].[dev_zona_servicios] ([zona_id], [tipo_servicio_id]) ON DELETE CASCADE,
    CONSTRAINT [CK_dev_zona_servicio_horarios_dia]               CHECK ([dia_semana] BETWEEN 1 AND 7),
    CONSTRAINT [CK_dev_zona_servicio_horarios_hora]              CHECK ([hora_inicio] < [hora_fin])
);
GO
