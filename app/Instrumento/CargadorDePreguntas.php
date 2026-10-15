<?php

namespace App\Instrumento;

use Illuminate\Support\Facades\DB;

/**
 * Inserta preguntas y opciones en el catálogo. Es idempotente: una pregunta
 * que ya existe (por código) no se toca, de modo que correrlo dos veces, o
 * una vez como migración y otra como seeder, deja la misma base.
 */
class CargadorDePreguntas
{
    /** Instrumento vigente: dimensiones, capacidades y preguntas de producto. */
    public function cargarBase(): void
    {
        $datos = require database_path('data/catalogo_base.php');

        $orden = 0;
        foreach ($datos['dimensiones'] as $codigo => $nombre) {
            DB::table('dimensiones')->insertOrIgnore([
                'codigo' => $codigo, 'nombre' => $nombre, 'orden' => ++$orden,
            ]);
        }

        $dimensiones = DB::table('dimensiones')->pluck('id', 'codigo');

        foreach ($datos['capacidades'] as $codigo => [$dimension, $fase, $nombre, $preguntas]) {
            DB::table('capacidades')->insertOrIgnore([
                'dimension_id' => $dimensiones[$dimension],
                'codigo' => $codigo,
                'nombre' => $nombre,
                'fase' => $fase,
            ]);

            $capacidadId = DB::table('capacidades')->where('codigo', $codigo)->value('id');

            foreach ($preguntas as $i => $texto) {
                // Sin la columna "ruta": en la línea base todavía no existe.
                $this->insertarPregunta($capacidadId, "{$codigo}-P" . ($i + 1), $texto, $i + 1, null);
            }
        }
    }

    /** Preguntas de servicios, ancladas por código a capacidades ya existentes. */
    public function cargarServicios(): void
    {
        $porCapacidad = require database_path('data/preguntas_servicios.php');
        $capacidades = DB::table('capacidades')->pluck('id', 'codigo');

        foreach ($porCapacidad as $codigo => $preguntas) {
            if (! isset($capacidades[$codigo])) {
                continue; // base sin catálogo todavía: las carga el seeder
            }
            foreach ($preguntas as $i => $texto) {
                $this->insertarPregunta($capacidades[$codigo], "{$codigo}-S" . ($i + 1), $texto, $i + 1, 'servicio');
            }
        }
    }

    private function insertarPregunta(int $capacidadId, string $codigo, string $texto, int $orden, ?string $ruta): void
    {
        if (DB::table('preguntas')->where('codigo', $codigo)->exists()) {
            return;
        }

        $fila = ['capacidad_id' => $capacidadId, 'codigo' => $codigo, 'texto' => $texto, 'orden' => $orden];
        if ($ruta !== null) {
            $fila['ruta'] = $ruta;
        }

        $preguntaId = DB::table('preguntas')->insertGetId($fila);

        foreach (EscalaRespuesta::OPCIONES as $puntaje => $etiqueta) {
            DB::table('opciones')->insert([
                'pregunta_id' => $preguntaId, 'orden' => $puntaje + 1, 'texto' => $etiqueta, 'puntaje' => $puntaje,
            ]);
        }
    }
}
