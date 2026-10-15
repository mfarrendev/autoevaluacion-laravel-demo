<?php

namespace App\Instrumento;

/**
 * El algoritmo de puntaje. Es una función pura: recibe capacidades y
 * respuestas y devuelve puntajes. No lee la base de datos y no conoce la
 * ruta, y por eso agregar la ruta de servicios no puede cambiar un resultado.
 *
 *   capacidad  = promedio de los puntajes de sus preguntas respondidas
 *   dimensión  = promedio de las capacidades que tienen respuesta
 *   fase       = la mayor fase f tal que TODAS las capacidades de las fases
 *                1..f tienen puntaje y llegan al umbral
 *
 * Si se cambia cualquier regla de arriba hay que cambiarla también en la
 * columna "regla_puntaje" de `php artisan matriz:trazabilidad`, y el test
 * RegresionMigracionesTest (que compara contra números guardados) va a caer a
 * propósito hasta que alguien decida que el cambio es intencional.
 */
final class CalculadoraPuntaje
{
    public const UMBRAL_FASE = 2.0;
    public const FASES = 4;

    /**
     * @param  array<int, array{id:int, dimension_id:int, fase:int}>  $capacidades
     * @param  array<int, array{capacidad_id:int, puntaje:int}>  $respuestas
     * @return array{capacidades: array<int, ?float>, dimensiones: array<int, ?float>, fase: int}
     */
    public function calcular(array $capacidades, array $respuestas): array
    {
        $porCapacidad = [];
        foreach ($respuestas as $r) {
            $porCapacidad[$r['capacidad_id']][] = $r['puntaje'];
        }

        $puntajeCapacidad = [];
        $porDimension = [];
        foreach ($capacidades as $c) {
            $p = $porCapacidad[$c['id']] ?? null;
            $puntajeCapacidad[$c['id']] = $p === null ? null : round(array_sum($p) / count($p), 2);
            if ($puntajeCapacidad[$c['id']] !== null) {
                $porDimension[$c['dimension_id']][] = $puntajeCapacidad[$c['id']];
            }
        }

        $puntajeDimension = [];
        foreach ($capacidades as $c) {
            $d = $porDimension[$c['dimension_id']] ?? null;
            $puntajeDimension[$c['dimension_id']] = $d === null ? null : round(array_sum($d) / count($d), 2);
        }

        $fase = 0;
        for ($f = 1; $f <= self::FASES; $f++) {
            foreach ($capacidades as $c) {
                if ($c['fase'] <= $f) {
                    $p = $puntajeCapacidad[$c['id']];
                    if ($p === null || $p < self::UMBRAL_FASE) {
                        break 2;
                    }
                }
            }
            $fase = $f;
        }

        return ['capacidades' => $puntajeCapacidad, 'dimensiones' => $puntajeDimension, 'fase' => $fase];
    }
}
