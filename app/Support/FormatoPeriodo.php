<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Texto del período de un reporte ("1 de Julio a 15 de Julio de 2026"). Se usa
 * en el PDF y el Excel v2 — siempre con fechas explícitas para no sugerir que
 * el rango cubre el mes completo cuando en realidad es parcial (período flexible).
 */
class FormatoPeriodo
{
    public static function texto(Carbon $desde, Carbon $hasta): string
    {
        $dia = fn (Carbon $fecha) => $fecha->day.' de '.ucfirst($fecha->translatedFormat('F'));

        if ($desde->isSameYear($hasta)) {
            return $dia($desde).' a '.$dia($hasta).' de '.$hasta->format('Y');
        }

        return $dia($desde).' de '.$desde->format('Y').' a '.$dia($hasta).' de '.$hasta->format('Y');
    }
}
