<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConclusionesAIService
{
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    private string $defaultPrompt = "Sos analista operativo de Infinito Reciclaje redactando la sección 'Análisis Estratégico' del informe mensual para el municipio. Período: {periodo}.\n\nDatos del período:\n- Viajes realizados: {total_viajes}\n- Toneladas netas recolectadas: {toneladas} t\n- Días operativos: {dias_op} de {dias_rango}\n- Productividad promedio: {promedio_ton_dia} t/día\n- Top 3 zonas por volumen: {top3_zonas}\n- Zonas de mayor densidad (kg/ha): {densidad_zonas}\n\nReferencias para evaluación:\n- Productividad alta: > 80 t/día | Media: 40–80 t/día | Baja: < 40 t/día\n- Tasa de actividad óptima: > 85 % de los días del período\n- Zona crítica: concentra más del 35 % del volumen total\n\nRedactá exactamente 3 párrafos separados por una línea en blanco:\nPárrafo 1 — Diagnóstico: evaluá si la productividad fue alta, media o baja; calculá la tasa de actividad e indicá si es óptima o deficitaria; nombrá la zona de mayor concentración y si su peso relativo exige revisión de frecuencia.\nPárrafo 2 — Posibilidades de mejora: identificá 2 oportunidades concretas derivadas de los datos (no genéricas). Cada una debe nombrar la zona o métrica que la justifica y describir qué acción la aprovecharía.\nPárrafo 3 — Recomendaciones para el próximo período: planteá 2 acciones prioritarias con foco operativo (rutas, frecuencias, recursos) que el municipio puede implementar de inmediato. Que sean específicas y accionables.\n\nEspañol formal. Sin encabezados, sin viñetas, sin numeración, sin saludos. Solo los tres párrafos separados por línea en blanco.";

    public function __construct(
        private string $apiKey,
        private string $modelo = 'gemini-2.5-flash',
        private string $prompt = '',
    ) {}

    public function generarAnalisis(array $kpis, Collection $zonas, string $periodo): string
    {
        $top3 = $zonas->take(3)->map(fn ($z) => "{$z['nombre']}: {$z['toneladas']} t ({$z['porcentaje']}%)")->join(', ');
        $conHa = $zonas->filter(fn ($z) => $z['kg_ha'] !== null)->sortByDesc('kg_ha')->take(3);
        $densidad = $conHa->isNotEmpty()
            ? $conHa->map(fn ($z) => "{$z['nombre']}: {$z['kg_ha']} kg/ha")->join(', ')
            : 'sin datos de superficie';

        $template = ! empty($this->prompt) ? $this->prompt : $this->defaultPrompt;

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
        Log::info('[ConclusionesAI] Prompt enviado', [
            'modelo'  => $this->modelo,
            'chars'   => \strlen($prompt),
            'prompt'  => $prompt,
        ]);

        try {
            $response = Http::timeout(15)->post(
                "{$this->baseUrl}/{$this->modelo}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.7,
                        'maxOutputTokens' => 1024,
                        'thinkingConfig'  => ['thinkingBudget' => 0],
                    ],
                ]
            );

            Log::debug('[ConclusionesAI] Respuesta recibida', [
                'status'   => $response->status(),
                'body'     => $response->json(),
            ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text', '');
            }

            Log::warning('[ConclusionesAI] Respuesta no exitosa', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return '';
        } catch (\Throwable $e) {
            Log::error('[ConclusionesAI] Excepción al llamar a la API', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return '';
        }
    }
}
