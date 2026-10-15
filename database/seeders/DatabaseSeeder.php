<?php

namespace Database\Seeders;

use App\Instrumento\CargadorDePreguntas;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Instalación nueva: `php artisan migrate --seed`. La migración de
     * servicios corre antes de que exista el catálogo (no encuentra
     * capacidades y no hace nada), así que las preguntas de servicios se
     * cargan acá, después de la base. En una base que ya tiene catálogo basta
     * con `migrate`.
     */
    public function run(): void
    {
        $cargador = new CargadorDePreguntas();
        $cargador->cargarBase();
        $cargador->cargarServicios();
    }
}
