<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MatrizTrazabilidadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_pasa_cuando_cada_capacidad_tiene_preguntas_en_las_dos_rutas(): void
    {
        $this->artisan('matriz:trazabilidad')->assertSuccessful();
    }

    public function test_falla_si_una_capacidad_se_queda_sin_preguntas_de_servicios(): void
    {
        $capacidad = DB::table('capacidades')->where('codigo', 'MER-1')->value('id');
        DB::table('preguntas')->where('capacidad_id', $capacidad)->where('ruta', 'servicio')->delete();

        $this->artisan('matriz:trazabilidad')->assertFailed();
    }

    public function test_exporta_una_fila_por_pregunta_con_su_capacidad_dimension_fase_y_regla(): void
    {
        $archivo = tempnam(sys_get_temp_dir(), 'matriz');
        $this->artisan('matriz:trazabilidad', ['--csv' => $archivo])->assertSuccessful();

        $filas = array_map('str_getcsv', file($archivo, FILE_IGNORE_NEW_LINES));
        unlink($archivo);

        $this->assertSame(['ruta', 'item', 'capacidad', 'dimension', 'fase', 'regla_puntaje'], $filas[0]);
        $this->assertCount(1 + 64, $filas); // encabezado + 32 de producto + 32 de servicios
        $this->assertContains('EQP-1-S1', array_column($filas, 1));
        $this->assertStringContainsString('fase exige >= 2.0', $filas[1][5]);
    }
}
