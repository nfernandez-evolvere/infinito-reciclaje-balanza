<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $organizacion_id
 * @property int|null $user_id
 * @property string $tipo
 * @property string $titulo
 * @property string|null $descripcion
 * @property int|null $pesaje_id
 * @property int|null $zona_id
 * @property int|null $reporte_generado_id
 * @property \Illuminate\Support\Carbon $fecha_deteccion
 * @property bool $leida
 * @property \Illuminate\Support\Carbon|null $leida_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $leidaPor
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \App\Models\Pesaje|null $pesaje
 * @property-read \App\Models\ReporteGenerado|null $reporteGenerado
 * @property-read \App\Models\Zona|null $zona
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereFechaDeteccion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereLeida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereLeidaAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta wherePesajeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereReporteGeneradoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereTitulo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Alerta whereZonaId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperAlerta {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $tipo
 * @property bool $activo
 * @property float|null $umbral_valor
 * @property string|null $hora_inicio
 * @property string|null $hora_fin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organizacion $organizacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereHoraFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereHoraInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereUmbralValor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigAlerta whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperConfigAlerta {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nombre
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TipoServicio> $tiposServicio
 * @property-read int|null $tipos_servicio_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TipoVehiculo> $tiposVehiculo
 * @property-read int|null $tipos_vehiculo_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vehiculo> $vehiculos
 * @property-read int|null $vehiculos_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Zona> $zonas
 * @property-read int|null $zonas_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion activas()
 * @method static \Database\Factories\OrganizacionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrganizacion {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $uuid
 * @property int $organizacion_id
 * @property int $vehiculo_id
 * @property int $operador_id
 * @property int $tipo_servicio_id
 * @property int $zona_id
 * @property string|null $turno
 * @property int $peso_bruto_kg
 * @property int $peso_tara_kg
 * @property int $peso_neto_kg
 * @property bool $alerta_peso
 * @property string|null $observaciones
 * @property string $estado
 * @property \Illuminate\Support\Carbon|null $hora_salida
 * @property int|null $bruto_salida_kg
 * @property bool $editado
 * @property string|null $motivo_cancelacion
 * @property int|null $cancelado_por_id
 * @property \Illuminate\Support\Carbon|null $cancelado_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $canceladoPor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PesajeLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\User $operador
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \App\Models\TipoServicio $tipoServicio
 * @property-read \App\Models\Vehiculo $vehiculo
 * @property-read \App\Models\Zona $zona
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje delTurno()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje enPredio()
 * @method static \Database\Factories\PesajeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereAlertaPeso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereBrutoSalidaKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereCanceladoAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereCanceladoPorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereEditado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereHoraSalida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereMotivoCancelacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereOperadorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje wherePesoBrutoKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje wherePesoNetoKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje wherePesoTaraKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereTipoServicioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereTurno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereVehiculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pesaje whereZonaId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPesaje {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $pesaje_id
 * @property string $campo
 * @property string|null $valor_anterior
 * @property string|null $valor_nuevo
 * @property string $motivo
 * @property int $usuario_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pesaje $pesaje
 * @property-read \App\Models\User $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereCampo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog wherePesajeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereValorAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesajeLog whereValorNuevo($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPesajeLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $municipalidad_nombre
 * @property string|null $intro_empresa
 * @property array<array-key, mixed>|null $servicios
 * @property bool $ai_enabled
 * @property string $ai_proveedor
 * @property string|null $ai_api_key
 * @property string $ai_modelo
 * @property string|null $ai_prompt
 * @property bool $tipo_informe_mensual_activo
 * @property bool $tipo_alertas_activo
 * @property bool $revision_requerida
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property array<array-key, mixed>|null $secciones
 * @property-read \App\Models\Organizacion $organizacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereAiApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereAiEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereAiModelo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereAiPrompt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereAiProveedor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereIntroEmpresa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereMunicipalidadNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereRevisionRequerida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereSecciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereServicios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereTipoAlertasActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereTipoInformeMensualActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteConfiguracion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReporteConfiguracion {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $email
 * @property string|null $nombre
 * @property int $uso_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organizacion $organizacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteDestinatario whereUsoCount($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReporteDestinatario {}
}

namespace App\Models{
/**
 * Registro de cada reporte producido: una descarga manual (Excel/PDF) o un
 * envío programado. Guarda los metadatos (período, filtros, formato,
 * destinatarios congelados) y un `snapshot` con los datos tal como se generó:
 * así una re-descarga o un envío aprobado reproduce el reporte idéntico al
 * original, sin recalcular sobre los pesajes vivos (la tara de un vehículo
 * puede cambiar después). Las entradas previas a la introducción del snapshot
 * lo tienen en null y caen al recálculo bajo demanda.
 *
 * Estados del flujo programado: generando → en_revision|enviando → enviado;
 * en_revision → descartado; cualquier fase puede caer a fallido y reintentarse.
 * Las descargas manuales usan el estado legacy `generado`.
 *
 * @property int $id
 * @property int $organizacion_id
 * @property int|null $usuario_id
 * @property int|null $reporte_programado_id
 * @property string $origen
 * @property string $tipo
 * @property string $formato
 * @property \Illuminate\Support\Carbon $periodo_desde
 * @property \Illuminate\Support\Carbon $periodo_hasta
 * @property array<array-key, mixed>|null $filtros
 * @property array<array-key, mixed>|null $destinatarios
 * @property string $estado
 * @property string|null $error
 * @property string|null $conclusiones
 * @property array<array-key, mixed>|null $snapshot
 * @property int|null $revisado_por_id
 * @property \Illuminate\Support\Carbon|null $revisado_at
 * @property \Illuminate\Support\Carbon|null $enviado_at
 * @property string|null $motivo_descarte
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \App\Models\ReporteProgramado|null $programado
 * @property-read \App\Models\User|null $revisadoPor
 * @property-read \App\Models\User|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereConclusiones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereDestinatarios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereEnviadoAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereFiltros($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereFormato($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereMotivoDescarte($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereOrigen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado wherePeriodoDesde($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado wherePeriodoHasta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereReporteProgramadoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereRevisadoAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereRevisadoPorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereSnapshot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteGenerado whereUsuarioId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReporteGenerado {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property int|null $creado_por_id
 * @property string $tipo
 * @property string $nombre
 * @property string $frecuencia
 * @property string $cron_expresion
 * @property array<array-key, mixed> $destinatarios
 * @property array<array-key, mixed>|null $opciones
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $ultimo_envio_at
 * @property \Illuminate\Support\Carbon|null $proximo_envio_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $inicio_en
 * @property-read \App\Models\User|null $creadoPor
 * @property-read \App\Models\Organizacion $organizacion
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado activos()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereCreadoPorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereCronExpresion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereDestinatarios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereFrecuencia($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereInicioEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereOpciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereProximoEnvioAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereUltimoEnvioAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReporteProgramado whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperReporteProgramado {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $nombre
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $descripcion
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TipoVehiculo> $tiposVehiculo
 * @property-read int|null $tipos_vehiculo_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Zona> $zonas
 * @property-read int|null $zonas_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio activos()
 * @method static \Database\Factories\TipoServicioFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereDescripcion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoServicio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTipoServicio {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $nombre
 * @property int $peso_min_kg
 * @property int $peso_max_kg
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read mixed $peso_tope_kg
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo activos()
 * @method static \Database\Factories\TipoVehiculoFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo wherePesoMaxKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo wherePesoMinKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TipoVehiculo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTipoVehiculo {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property bool $onboarding_visto
 * @property bool $activo
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organizacion> $organizaciones
 * @property-read int|null $organizaciones_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOnboardingVisto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property string $patente
 * @property string|null $numero_interno
 * @property int $tara_kg
 * @property int $tipo_vehiculo_id
 * @property string $titular
 * @property int|null $capacidad_kg
 * @property string|null $observaciones
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VehiculoLog> $logs
 * @property-read int|null $logs_count
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pesaje> $pesajes
 * @property-read int|null $pesajes_count
 * @property-read \App\Models\TipoVehiculo $tipoVehiculo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo activos()
 * @method static \Database\Factories\VehiculoFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereCapacidadKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereNumeroInterno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereObservaciones($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo wherePatente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereTaraKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereTipoVehiculoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereTitular($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vehiculo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperVehiculo {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $vehiculo_id
 * @property string $campo
 * @property string|null $valor_anterior
 * @property string|null $valor_nuevo
 * @property string $motivo
 * @property int $usuario_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $usuario
 * @property-read \App\Models\Vehiculo $vehiculo
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereCampo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereMotivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereValorAnterior($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereValorNuevo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VehiculoLog whereVehiculoId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperVehiculoLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $organizacion_id
 * @property int $tipo_servicio_id
 * @property string $nombre
 * @property float|null $hectareas
 * @property int|null $barrios
 * @property int|null $habitantes
 * @property string|null $geojson
 * @property float|null $centro_lat
 * @property float|null $centro_lng
 * @property bool $activo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read array $horarios_por_dia
 * @property-read array $turnos_array
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ZonaHorario> $horarios
 * @property-read int|null $horarios_count
 * @property-read \App\Models\Organizacion $organizacion
 * @property-read \App\Models\TipoServicio $tipoServicio
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ZonaTurno> $turnos
 * @property-read int|null $turnos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona activos()
 * @method static \Database\Factories\ZonaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereActivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereBarrios($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereCentroLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereCentroLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereGeojson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereHabitantes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereHectareas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereNombre($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereOrganizacionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereTipoServicioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Zona whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperZona {}
}

namespace App\Models{
/**
 * @property int $zona_id
 * @property int $dia_semana
 * @property int $franja
 * @property string $hora_inicio
 * @property string $hora_fin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario whereDiaSemana($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario whereFranja($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario whereHoraFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario whereHoraInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaHorario whereZonaId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperZonaHorario {}
}

namespace App\Models{
/**
 * @property int $zona_id
 * @property string $turno
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaTurno newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaTurno newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaTurno query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaTurno whereTurno($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ZonaTurno whereZonaId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperZonaTurno {}
}

