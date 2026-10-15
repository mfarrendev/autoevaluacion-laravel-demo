<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Línea base 2/2: lo que guardan los usuarios (respuestas y resultados).
 * Esta es la base de datos "de resultados" que la ruta de servicios no debe
 * alterar: las migraciones posteriores solo agregan una columna con valor por
 * defecto y filas de catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autoevaluaciones', function (Blueprint $t) {
            $t->id();
            $t->string('organizacion');
            $t->string('estado', 12)->default('en_curso');
            $t->unsignedTinyInteger('fase_alcanzada')->nullable();
            $t->timestamps();
        });

        Schema::create('respuestas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('autoevaluacion_id')->constrained('autoevaluaciones')->cascadeOnDelete();
            $t->foreignId('pregunta_id')->constrained('preguntas');
            $t->foreignId('opcion_id')->constrained('opciones');
            $t->unique(['autoevaluacion_id', 'pregunta_id']);
        });

        Schema::create('resultados', function (Blueprint $t) {
            $t->id();
            $t->foreignId('autoevaluacion_id')->constrained('autoevaluaciones')->cascadeOnDelete();
            $t->foreignId('dimension_id')->constrained('dimensiones');
            $t->decimal('puntaje', 4, 2)->nullable();
            $t->unique(['autoevaluacion_id', 'dimension_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultados');
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('autoevaluaciones');
    }
};
