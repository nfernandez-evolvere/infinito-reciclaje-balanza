<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class ConclusionesAIService
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    private string $defaultPrompt = "Sos analista operativo de Infinito Reciclaje redactando la sección 'Oportunidades Estratégicas' del informe mensual para el municipio. Período: {periodo}.\n\nDatos del período:\n- Viajes realizados: {total_viajes}\n- Toneladas netas recolectadas: {toneladas} t\n- Días operativos: {dias_op} de {dias_rango}\n- Productividad promedio: {promedio_ton_dia} t/día\n- Top 3 zonas por volumen: {top3_zonas}\n- Zonas de mayor densidad (kg/ha): {densidad_zonas}\n\nReferencias para evaluación:\n- Productividad alta: > 80 t/día | Media: 40–80 t/día | Baja: < 40 t/día\n- Tasa de actividad óptima: > 85 % de los días del período\n- Zona crítica: concentra más del 35 % del volumen total\n\nRedactá exactamente 2 párrafos:\nPárrafo 1 — Diagnóstico: usá los datos y las referencias para evaluar si la productividad fue alta, media o baja; calculá la tasa de actividad e indicá si es óptima o deficitaria; nombrá la zona más crítica y si su concentración exige ajuste de frecuencia.\nPárrafo 2 — Oportunidades: planteá 2 acciones específicas y accionables para el próximo período, derivadas directamente de los datos (no genéricas). Cada acción debe nombrar la zona o métrica concreta que la justifica.\n\nEspañol formal. Sin encabezados, sin viñetas, sin saludos. Solo los dos párrafos.";

    public function __construct(
        private string $apiKey,
        private string $modelo = 'gemini-2.5-flash',
        private string $prompt = '',
    ) {}

    public function generarAnalisis(array $kpis, Collection $zonas, string $periodo): string
    {
        $top3     = $zonas->take(3)->map(fn ($z) => "{$z['nombre']}: {$z['toneladas']} t ({$z['porcentaje']}%)")->join(', ');
        $conHa    = $zonas->filter(fn ($z) => $z['kg_ha'] !== null)->sortByDesc('kg_ha')->take(3);
        $densidad = $conHa->isNotEmpty()
            ? $conHa->map(fn ($z) => "{$z['nombre']}: {$z['kg_ha']} kg/ha")->join(', ')
            : 'sin datos de superficie';

        $template = !empty($this->prompt) ? $this->prompt : $this->defaultPrompt;

        $text = str_replace(
            ['{periodo}', '{total_viajes}', '{toneladas}', '{dias_op}', '{dias_rango}', '{promedio_ton_dia}', '{top3_zonas}', '{densidad_zonas}'],
            [$periodo, $kpis['total'], $kpis['toneladas'], $kpis['dias_op'], $kpis['dias_rango'], $kpis['promedio_ton_dia'], $top3, $densidad],
            $template
        );

        return $this->llamar($text);
    }

    public static function variablesDisponibles(): array
    {
        return ['{periodo}', '{total_viajes}', '{toneladas}', '{dias_op}', '{dias_rango}', '{promedio_ton_dia}', '{top3_zonas}', '{densidad_zonas}'];
    }

    private function llamar(string $prompt): string
    {
        try {
            $response = Http::timeout(15)->post(
                "{$this->baseUrl}/{$this->modelo}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 700,
                    ],
                ]
            );

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text', '');
            }

            return '';
        } catch (\Throwable) {
            return '';
        }
    }
}
