<?php

namespace App\Console\Commands;

use App\Enums\Ruta;
use App\Instrumento\CalculadoraPuntaje;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MatrizTrazabilidad extends Command
{
    protected $signature = 'matriz:trazabilidad {--csv= : archivo donde guardar la matriz ítem-capacidad-dimensión-fase}';

    protected $description = 'Verifica que cada capacidad tenga fuente de información en cada ruta y exporta la matriz de trazabilidad';

    public function handle(): int
    {
        $capacidades = DB::table('capacidades')
            ->join('dimensiones', 'dimensiones.id', '=', 'capacidades.dimension_id')
            ->orderBy('dimensiones.orden')->orderBy('capacidades.id')
            ->get(['capacidades.id', 'capacidades.codigo', 'capacidades.nombre', 'capacidades.fase',
                'dimensiones.nombre as dimension']);

        $filas = [];
        $faltantes = 0;
        foreach ($capacidades as $c) {
            $fila = [$c->codigo, $c->dimension, $c->fase];
            foreach (Ruta::cases() as $ruta) {
                $n = DB::table('preguntas')->where('capacidad_id', $c->id)->where('ruta', $ruta->value)->count();
                $fila[] = $n;
                if ($n === 0) {
                    $faltantes++;
                }
            }
            $fila[] = in_array(0, array_slice($fila, 3), true) ? 'FALTA' : 'ok';
            $filas[] = $fila;
        }

        $this->table(['Capacidad', 'Dimensión', 'Fase', 'Preguntas producto', 'Preguntas servicio', 'Estado'], $filas);

        if ($csv = $this->option('csv')) {
            $this->exportar($csv);
            $this->info("Matriz guardada en {$csv}");
        }

        if ($faltantes > 0) {
            $this->error("{$faltantes} combinaciones capacidad-ruta no tienen ninguna pregunta.");

            return self::FAILURE;
        }

        $this->info('Todas las capacidades tienen preguntas en las dos rutas.');

        return self::SUCCESS;
    }

    private function exportar(string $archivo): void
    {
        $regla = sprintf(
            'capacidad = promedio de sus preguntas; dimension = promedio de capacidades; fase exige >= %.1f en todas las capacidades hasta esa fase',
            CalculadoraPuntaje::UMBRAL_FASE
        );

        $manejador = fopen($archivo, 'w');
        fputcsv($manejador, ['ruta', 'item', 'capacidad', 'dimension', 'fase', 'regla_puntaje'], ',', '"', '');

        $filas = DB::table('preguntas')
            ->join('capacidades', 'capacidades.id', '=', 'preguntas.capacidad_id')
            ->join('dimensiones', 'dimensiones.id', '=', 'capacidades.dimension_id')
            ->orderBy('preguntas.ruta')->orderBy('dimensiones.orden')->orderBy('capacidades.id')->orderBy('preguntas.orden')
            ->get(['preguntas.ruta', 'preguntas.codigo', 'capacidades.codigo as capacidad',
                'dimensiones.nombre as dimension', 'capacidades.fase']);

        foreach ($filas as $f) {
            fputcsv($manejador, [$f->ruta, $f->codigo, $f->capacidad, $f->dimension, $f->fase, $regla], ',', '"', '');
        }
        fclose($manejador);
    }
}
