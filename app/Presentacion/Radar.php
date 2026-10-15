<?php

namespace App\Presentacion;

/**
 * Geometría del gráfico de radar (SVG). Va aparte de la vista para poder
 * probarla: un puntaje nulo se dibuja en el centro, no se omite, para que el
 * polígono siempre tenga un vértice por dimensión.
 */
final class Radar
{
    /**
     * @param  array<int, ?float>  $valores  un valor por eje, de 0 a $maximo
     * @return array<int, array{0: float, 1: float}>  vértices (x, y)
     */
    public static function vertices(array $valores, float $maximo, float $radio, float $cx, float $cy): array
    {
        $n = count($valores);
        $puntos = [];
        foreach (array_values($valores) as $i => $valor) {
            $angulo = -M_PI / 2 + 2 * M_PI * $i / $n;
            $r = $radio * min(max($valor ?? 0.0, 0.0), $maximo) / $maximo;
            $puntos[] = [round($cx + $r * cos($angulo), 2), round($cy + $r * sin($angulo), 2)];
        }

        return $puntos;
    }

    /** Atributo `points` de un <polygon>. */
    public static function poligono(array $vertices): string
    {
        return implode(' ', array_map(fn ($p) => "{$p[0]},{$p[1]}", $vertices));
    }
}
