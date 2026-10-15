<?php

namespace App\Instrumento;

use Illuminate\Support\Facades\DB;

/**
 * Lee las respuestas de una autoevaluación, corre el algoritmo y guarda el
 * resultado. Usa solo columnas que existen desde la línea base (nunca
 * "ruta"), así que funciona igual antes y después de la migración de
 * servicios: eso es lo que permite probar que los resultados no cambian.
 */
class ServicioDeResultados
{
    public function __construct(private CalculadoraPuntaje $calculadora = new CalculadoraPuntaje())
    {
    }

    public function recalcular(int $autoevaluacionId): array
    {
        $capacidades = DB::table('capacidades')
            ->orderBy('id')
            ->get(['id', 'dimension_id', 'fase'])
            ->map(fn ($c) => (array) $c)
            ->all();

        $respuestas = DB::table('respuestas')
            ->join('preguntas', 'preguntas.id', '=', 'respuestas.pregunta_id')
            ->join('opciones', 'opciones.id', '=', 'respuestas.opcion_id')
            ->where('respuestas.autoevaluacion_id', $autoevaluacionId)
            ->get(['preguntas.capacidad_id', 'opciones.puntaje'])
            ->map(fn ($r) => ['capacidad_id' => $r->capacidad_id, 'puntaje' => $r->puntaje])
            ->all();

        $calculo = $this->calculadora->calcular($capacidades, $respuestas);

        DB::transaction(function () use ($autoevaluacionId, $calculo) {
            foreach ($calculo['dimensiones'] as $dimensionId => $puntaje) {
                DB::table('resultados')->updateOrInsert(
                    ['autoevaluacion_id' => $autoevaluacionId, 'dimension_id' => $dimensionId],
                    ['puntaje' => $puntaje],
                );
            }
            DB::table('autoevaluaciones')->where('id', $autoevaluacionId)
                ->update(['fase_alcanzada' => $calculo['fase']]);
        });

        return $calculo;
    }
}
