<?php

use App\Instrumento\CargadorDePreguntas;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * Carga las preguntas de la ruta de servicios (dos por capacidad) y sus
 * opciones de respuesta. Ancla cada pregunta a una capacidad existente por
 * código; si la base está vacía (instalación nueva, sin catálogo todavía) no
 * hace nada y las carga el seeder.
 *
 * Reversa: borra solo las preguntas de servicios, y solo si nadie las ha
 * respondido todavía. Si ya hay respuestas, aborta sin tocar nada: borrar
 * respuestas de usuarios nunca es parte de una reversa.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new CargadorDePreguntas())->cargarServicios();
    }

    public function down(): void
    {
        $preguntas = DB::table('preguntas')->where('ruta', 'servicio')->pluck('id');

        if (DB::table('respuestas')->whereIn('pregunta_id', $preguntas)->exists()) {
            throw new RuntimeException(
                'No se puede revertir: hay respuestas guardadas sobre preguntas de servicios.'
            );
        }

        DB::table('preguntas')->whereIn('id', $preguntas)->delete();
    }
};
