<?php

namespace Tests\Feature;

use App\Instrumento\ServicioDeResultados;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\InstrumentoDeEjemplo;
use Tests\TestCase;

/**
 * La prueba que importa: agregar la ruta de servicios NO altera lo que ya
 * existía. Se arma la base como estaba antes (migraciones 1 y 2), se crean
 * autoevaluaciones ya respondidas (una de ellas a medio responder), se aplican
 * las migraciones nuevas y se comprueba, fila por fila, que nada cambió; luego
 * se revierte y se comprueba otra vez.
 *
 * No usa RefreshDatabase: maneja las migraciones a mano. Al terminar deja la
 * base con todas las migraciones y sin datos, que es lo que esperan las demás.
 *
 * Cómo se midieron las pruebas: ver el README, sección "Cómo se midieron las
 * pruebas" (defectos puestos a mano y cuántos hicieron caer la suite).
 */
class RegresionMigracionesTest extends TestCase
{
    private const LINEA_BASE = [
        'database/migrations/2026_10_15_000001_crear_catalogo.php',
        'database/migrations/2026_10_15_000002_crear_autoevaluaciones.php',
    ];

    private const GOLDEN = __DIR__ . '/../fixtures/resultados_linea_base.json';

    protected function tearDown(): void
    {
        Artisan::call('migrate:fresh');
        parent::tearDown();
    }

    private function levantarLaLineaBase(): array
    {
        Artisan::call('migrate:fresh', ['--path' => self::LINEA_BASE]);
        $this->assertFalse(Schema::hasColumn('preguntas', 'ruta'), 'la línea base no debe tener ruta todavía');

        // El catálogo vigente, tal como lo cargaría la producción de antes.
        (new \App\Instrumento\CargadorDePreguntas())->cargarBase();
        $ids = InstrumentoDeEjemplo::crearAutoevaluacionesDeLaLineaBase();
        foreach ($ids as $id) {
            app(ServicioDeResultados::class)->recalcular($id);
        }

        return $ids;
    }

    public function test_la_ruta_de_servicios_no_cambia_nada_de_lo_ya_guardado(): void
    {
        $ids = $this->levantarLaLineaBase();
        $antes = InstrumentoDeEjemplo::fotoDeLoGuardado();
        $this->assertCount(12, $antes['autoevaluaciones']);
        $this->assertCount(12 * 8, $antes['resultados']);

        Artisan::call('migrate');

        $this->assertTrue(Schema::hasColumn('autoevaluaciones', 'ruta'));
        $this->assertSame(32, DB::table('preguntas')->where('ruta', 'servicio')->count());
        $this->assertSame(32, DB::table('preguntas')->where('ruta', 'producto')->count());
        $this->assertSame(
            ['producto'],
            DB::table('autoevaluaciones')->distinct()->pluck('ruta')->all(),
            'todo lo que existía debe quedar en la ruta de producto'
        );

        $this->assertSame($antes, InstrumentoDeEjemplo::fotoDeLoGuardado(), 'respuestas y resultados deben seguir idénticos');
        $this->assertSame(
            'en_curso',
            DB::table('autoevaluaciones')->where('id', end($ids))->value('estado'),
            'la autoevaluación a medio responder sigue en curso'
        );

        // Recalcular con el código nuevo da lo mismo que quedó guardado.
        foreach ($ids as $id) {
            app(ServicioDeResultados::class)->recalcular($id);
        }
        $this->assertSame($antes, InstrumentoDeEjemplo::fotoDeLoGuardado(), 'el algoritmo no debe haber cambiado');

        $this->compararConLosNumerosGuardados($antes);
    }

    public function test_la_reversa_devuelve_la_base_a_como_estaba(): void
    {
        $this->levantarLaLineaBase();
        $antes = InstrumentoDeEjemplo::fotoDeLoGuardado();

        Artisan::call('migrate');
        Artisan::call('migrate:rollback', ['--step' => 2]);

        $this->assertFalse(Schema::hasColumn('preguntas', 'ruta'));
        $this->assertFalse(Schema::hasColumn('autoevaluaciones', 'ruta'));
        $this->assertSame(32, DB::table('preguntas')->count(), 'quedan solo las 32 preguntas de producto');
        $this->assertSame($antes, InstrumentoDeEjemplo::fotoDeLoGuardado());
    }

    public function test_la_reversa_se_niega_a_borrar_respuestas_de_servicios(): void
    {
        $this->levantarLaLineaBase();
        Artisan::call('migrate');

        $preguntaDeServicio = DB::table('preguntas')->where('ruta', 'servicio')->orderBy('id')->first();
        $opcion = DB::table('opciones')->where('pregunta_id', $preguntaDeServicio->id)->value('id');
        $a = DB::table('autoevaluaciones')->insertGetId([
            'organizacion' => 'Nueva de servicios', 'ruta' => 'servicio', 'estado' => 'en_curso',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('respuestas')->insert(['autoevaluacion_id' => $a, 'pregunta_id' => $preguntaDeServicio->id, 'opcion_id' => $opcion]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('hay respuestas guardadas');

        try {
            (require base_path('database/migrations/2026_10_15_000004_cargar_preguntas_servicios.php'))->down();
        } finally {
            $this->assertSame(1, DB::table('respuestas')->where('autoevaluacion_id', $a)->count(), 'no se borró nada');
            $this->assertSame(32, DB::table('preguntas')->where('ruta', 'servicio')->count());
        }
    }

    public function test_no_se_puede_quitar_la_columna_ruta_mientras_queden_servicios(): void
    {
        $this->levantarLaLineaBase();
        Artisan::call('migrate');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Deshaga antes la migración');

        (require base_path('database/migrations/2026_10_15_000003_agregar_ruta.php'))->down();
    }

    public function test_una_instalacion_nueva_con_seed_queda_igual_que_una_migrada(): void
    {
        Artisan::call('migrate:fresh');
        $this->assertSame(0, DB::table('preguntas')->count(), 'sin seed, la migración no inventa preguntas');

        $this->seed(DatabaseSeeder::class);
        $this->assertSame(32, DB::table('preguntas')->where('ruta', 'producto')->count());
        $this->assertSame(32, DB::table('preguntas')->where('ruta', 'servicio')->count());

        // Repetir el seed no duplica nada.
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(64, DB::table('preguntas')->count());
    }

    /**
     * Compara los puntajes contra un archivo guardado en el repositorio. Si
     * alguien cambia el algoritmo, esto cae aunque "antes" y "después" sigan
     * siendo iguales entre sí. Para aceptar un cambio intencional:
     * ACTUALIZAR_GOLDEN=1 vendor/bin/phpunit --filter RegresionMigraciones
     */
    private function compararConLosNumerosGuardados(array $foto): void
    {
        $actual = [
            'fase_por_autoevaluacion' => array_column($foto['autoevaluaciones'], 'fase_alcanzada', 'id'),
            'resultados' => array_map(fn ($r) => [$r['autoevaluacion_id'], $r['dimension_id'], $r['puntaje']], $foto['resultados']),
        ];

        if (getenv('ACTUALIZAR_GOLDEN') === '1') {
            file_put_contents(self::GOLDEN, json_encode($actual, JSON_PRETTY_PRINT) . "\n");
        }

        $this->assertFileExists(self::GOLDEN);
        $this->assertSame(
            json_decode(file_get_contents(self::GOLDEN), true),
            json_decode(json_encode($actual), true),
            'los puntajes ya no coinciden con los guardados en tests/fixtures/resultados_linea_base.json'
        );
    }
}
