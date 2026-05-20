-- =====================================================================
-- INFINITO RECICLAJE — BALANZA
-- Ambiente : DEV
-- Schema   : infinito_balanza
-- Prefijo  : dev_
-- Script   : 001 — Tablas internas del framework Laravel
-- =====================================================================
-- Tablas requeridas por Laravel para sesiones, caché y colas.
-- No contienen lógica de dominio del proyecto.
-- =====================================================================

-- Crear schema si no existe
IF NOT EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'infinito_balanza')
    EXEC('CREATE SCHEMA infinito_balanza');
GO

-- ---------------------------------------------------------------------
-- dev_sessions
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_sessions]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_sessions] (
    [id]            NVARCHAR(255) NOT NULL,
    [user_id]       BIGINT        NULL,
    [ip_address]    NVARCHAR(45)  NULL,
    [user_agent]    NVARCHAR(MAX) NULL,
    [payload]       NVARCHAR(MAX) NOT NULL,
    [last_activity] INT           NOT NULL,
    CONSTRAINT [PK_dev_sessions] PRIMARY KEY ([id])
);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_dev_sessions_user_id' AND object_id = OBJECT_ID(N'[infinito_balanza].[dev_sessions]'))
    CREATE INDEX [IX_dev_sessions_user_id] ON [infinito_balanza].[dev_sessions] ([user_id]);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_dev_sessions_last_activity' AND object_id = OBJECT_ID(N'[infinito_balanza].[dev_sessions]'))
    CREATE INDEX [IX_dev_sessions_last_activity] ON [infinito_balanza].[dev_sessions] ([last_activity]);
GO

-- ---------------------------------------------------------------------
-- dev_cache
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_cache]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_cache] (
    [key]        NVARCHAR(255) NOT NULL,
    [value]      NVARCHAR(MAX) NOT NULL,
    [expiration] BIGINT        NOT NULL,
    CONSTRAINT [PK_dev_cache] PRIMARY KEY ([key])
);
GO

-- ---------------------------------------------------------------------
-- dev_cache_locks
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_cache_locks]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_cache_locks] (
    [key]        NVARCHAR(255) NOT NULL,
    [owner]      NVARCHAR(255) NOT NULL,
    [expiration] BIGINT        NOT NULL,
    CONSTRAINT [PK_dev_cache_locks] PRIMARY KEY ([key])
);
GO

-- ---------------------------------------------------------------------
-- dev_jobs
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_jobs]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_jobs] (
    [id]           BIGINT IDENTITY(1,1) NOT NULL,
    [queue]        NVARCHAR(255)        NOT NULL,
    [payload]      NVARCHAR(MAX)        NOT NULL,
    [attempts]     SMALLINT             NOT NULL,
    [reserved_at]  INT                  NULL,
    [available_at] INT                  NOT NULL,
    [created_at]   INT                  NOT NULL,
    CONSTRAINT [PK_dev_jobs] PRIMARY KEY ([id])
);
GO

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_dev_jobs_queue' AND object_id = OBJECT_ID(N'[infinito_balanza].[dev_jobs]'))
    CREATE INDEX [IX_dev_jobs_queue] ON [infinito_balanza].[dev_jobs] ([queue]);
GO

-- ---------------------------------------------------------------------
-- dev_job_batches
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_job_batches]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_job_batches] (
    [id]             NVARCHAR(255) NOT NULL,
    [name]           NVARCHAR(255) NOT NULL,
    [total_jobs]     INT           NOT NULL,
    [pending_jobs]   INT           NOT NULL,
    [failed_jobs]    INT           NOT NULL,
    [failed_job_ids] NVARCHAR(MAX) NOT NULL,
    [options]        NVARCHAR(MAX) NULL,
    [cancelled_at]   INT           NULL,
    [created_at]     INT           NOT NULL,
    [finished_at]    INT           NULL,
    CONSTRAINT [PK_dev_job_batches] PRIMARY KEY ([id])
);
GO

-- ---------------------------------------------------------------------
-- dev_failed_jobs
-- ---------------------------------------------------------------------
IF NOT EXISTS (SELECT 1 FROM sys.objects WHERE object_id = OBJECT_ID(N'[infinito_balanza].[dev_failed_jobs]') AND type = 'U')
CREATE TABLE [infinito_balanza].[dev_failed_jobs] (
    [id]         BIGINT IDENTITY(1,1) NOT NULL,
    [uuid]       NVARCHAR(255)        NOT NULL,
    [connection] NVARCHAR(MAX)        NOT NULL,
    [queue]      NVARCHAR(MAX)        NOT NULL,
    [payload]    NVARCHAR(MAX)        NOT NULL,
    [exception]  NVARCHAR(MAX)        NOT NULL,
    [failed_at]  DATETIME2(0)         NOT NULL DEFAULT GETDATE(),
    CONSTRAINT [PK_dev_failed_jobs] PRIMARY KEY ([id]),
    CONSTRAINT [UQ_dev_failed_jobs_uuid] UNIQUE ([uuid])
);
GO
