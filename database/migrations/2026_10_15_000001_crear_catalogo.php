<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Línea base 1/2: el instrumento que ya existe en producción (catálogo).
 * Todavía no hay "ruta": todas las preguntas son del producto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dimensiones', function (Blueprint $t) {
            $t->id();
            $t->string('codigo', 8)->unique();
            $t->string('nombre');
            $t->unsignedTinyInteger('orden');
        });

        Schema::create('capacidades', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dimension_id')->constrained('dimensiones');
            $t->string('codigo', 8)->unique();
            $t->string('nombre');
            $t->unsignedTinyInteger('fase');
        });

        Schema::create('preguntas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('capacidad_id')->constrained('capacidades');
            $t->string('codigo', 16)->unique();
            $t->text('texto');
            $t->unsignedTinyInteger('orden');
        });

        Schema::create('opciones', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pregunta_id')->constrained('preguntas')->cascadeOnDelete();
            $t->unsignedTinyInteger('orden');
            $t->string('texto');
            $t->unsignedTinyInteger('puntaje');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opciones');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('capacidades');
        Schema::dropIfExists('dimensiones');
    }
};
