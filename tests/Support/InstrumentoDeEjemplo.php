<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;

/**
 * Datos de prueba que funcionan con la estructura de la línea base y con la
 * posterior: solo usan columnas que existen desde la migración 2, nunca
 * "ruta". Así se pueden crear ANTES de migrar a servicios y comparar después.
 */
final class InstrumentoDeEjemplo
{
    /**
     * Crea $cuantas autoevaluaciones con respuestas deterministas (sin azar,
     * para que el archivo de resultados guardado sea estable). La autoevaluación
     * k apunta a la fase (k mod 5): las capacidades de esa fase o menos llevan
     * 3 o 2 (pasan el umbral) y las demás 1 o 0, de modo que se ejercitan las
     * cinco fases posibles (0 a 4). La última queda en curso con solo la mitad
     * de las preguntas respondidas.
     *
     * @return int[] ids de las autoevaluaciones
     */
    public static function crearAutoevaluacionesDeLaLineaBase(int $cuantas = 12): array
    {
        $preguntas = DB::table('preguntas')
            ->join('capacidades', 'capacidades.id', '=', 'preguntas.capacidad_id')
            ->orderBy('preguntas.id')->get(['preguntas.id', 'capacidades.fase']);
        $opcionesPorPregunta = DB::table('opciones')->orderBy('pregunta_id')->orderBy('orden')
            ->get(['id', 'pregunta_id'])->groupBy('pregunta_id');

        $ids = [];
        for ($k = 1; $k <= $cuantas; $k++) {
            $enCurso = $k === $cuantas;
            $id = DB::table('autoevaluaciones')->insertGetId([
                'organizacion' => "Organización {$k}",
                'estado' => $enCurso ? 'en_curso' : 'completada',
                'created_at' => '2026-09-01 10:00:00',
                'updated_at' => '2026-09-01 10:00:00',
            ]);

            foreach ($preguntas as $i => $pregunta) {
                if ($enCurso && $i % 2 === 1) {
                    continue;
                }
                $alcanza = $pregunta->fase <= $k % 5;
                $puntaje = $alcanza ? 2 + ($i % 2) : $i % 2;   // 3 o 2, o bien 1 o 0
                DB::table('respuestas')->insert([
                    'autoevaluacion_id' => $id,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionesPorPregunta[$pregunta->id][$puntaje]->id,
                ]);
            }
            $ids[] = $id;
        }

        return $ids;
    }

    /** Todo lo que un usuario guardó, en un orden fijo, para compararlo. */
    public static function fotoDeLoGuardado(): array
    {
        return [
            'autoevaluaciones' => DB::table('autoevaluaciones')->orderBy('id')
                ->get(['id', 'organizacion', 'estado', 'fase_alcanzada', 'created_at'])->map(fn ($f) => (array) $f)->all(),
            'respuestas' => DB::table('respuestas')->orderBy('id')
                ->get(['autoevaluacion_id', 'pregunta_id', 'opcion_id'])->map(fn ($f) => (array) $f)->all(),
            'resultados' => DB::table('resultados')->orderBy('autoevaluacion_id')->orderBy('dimension_id')
                ->get(['autoevaluacion_id', 'dimension_id', 'puntaje'])->map(fn ($f) => (array) $f)->all(),
        ];
    }
}
