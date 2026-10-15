<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Agrega la columna "ruta" a las preguntas y a las autoevaluaciones.
 *
 * El valor por defecto es 'producto': así todo lo que ya existe queda en la
 * ruta de producto sin tocar ninguna fila, y las autoevaluaciones ya
 * respondidas siguen siendo válidas.
 *
 * Reversa: en MySQL un cambio de estructura no se deshace solo si falla a
 * medias, así que down() se niega a borrar la columna mientras exista algo de
 * la ruta de servicios. Hay que deshacer primero la migración 4.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $t) {
            $t->string('ruta', 10)->default('producto')->after('capacidad_id');
            $t->index('ruta');
        });

        Schema::table('autoevaluaciones', function (Blueprint $t) {
            $t->string('ruta', 10)->default('producto')->after('organizacion');
        });
    }

    public function down(): void
    {
        $hayServicios = DB::table('preguntas')->where('ruta', 'servicio')->exists()
            || DB::table('autoevaluaciones')->where('ruta', 'servicio')->exists();

        if ($hayServicios) {
            throw new RuntimeException(
                'No se puede quitar la columna ruta: quedan preguntas o autoevaluaciones de servicios. '
                . 'Deshaga antes la migración 2026_10_15_000004_cargar_preguntas_servicios.'
            );
        }

        Schema::table('autoevaluaciones', function (Blueprint $t) {
            $t->dropColumn('ruta');
        });

        Schema::table('preguntas', function (Blueprint $t) {
            $t->dropIndex(['ruta']);
            $t->dropColumn('ruta');
        });
    }
};
