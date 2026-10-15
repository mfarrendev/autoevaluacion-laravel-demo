<?php

namespace Tests\Feature;

use App\Enums\Ruta;
use App\Models\Autoevaluacion;
use App\Models\Opcion;
use App\Models\Pregunta;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RutasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function crear(Ruta $ruta): Autoevaluacion
    {
        $this->post('/autoevaluaciones', ['organizacion' => 'Acme', 'ruta' => $ruta->value])->assertRedirect();

        return Autoevaluacion::latest('id')->firstOrFail();
    }

    /** Responde todas las preguntas de la ruta con la opción de puntaje $puntaje. */
    private function responderTodo(Autoevaluacion $a, int $puntaje, string $accion = 'finalizar')
    {
        $respuestas = Pregunta::where('ruta', $a->ruta->value)->get()->mapWithKeys(fn ($p) => [
            $p->id => Opcion::where('pregunta_id', $p->id)->where('puntaje', $puntaje)->value('id'),
        ])->all();

        return $this->put("/autoevaluaciones/{$a->id}", ['respuestas' => $respuestas, 'accion' => $accion]);
    }

    public function test_cada_ruta_muestra_solo_sus_preguntas(): void
    {
        $producto = $this->crear(Ruta::Producto);
        $this->get("/autoevaluaciones/{$producto->id}")
            ->assertOk()
            ->assertSee('trabaja en el proyecto con dedicación')
            ->assertDontSee('trabajan hoy en el servicio');

        $servicio = $this->crear(Ruta::Servicio);
        $this->get("/autoevaluaciones/{$servicio->id}")
            ->assertOk()
            ->assertSee('trabajan hoy en el servicio')
            ->assertDontSee('trabaja en el proyecto con dedicación');
    }

    public function test_las_dos_rutas_generan_el_reporte_con_las_ocho_dimensiones_y_la_misma_fase(): void
    {
        foreach ([Ruta::Producto, Ruta::Servicio] as $ruta) {
            $a = $this->crear($ruta);
            $this->responderTodo($a, 3)->assertRedirect("/autoevaluaciones/{$a->id}/reporte");

            $this->assertSame(8, $a->resultados()->count(), "ruta {$ruta->value}");
            $this->assertSame(4, $a->fresh()->fase_alcanzada, "ruta {$ruta->value}");
            $this->get("/autoevaluaciones/{$a->id}/reporte")
                ->assertOk()
                ->assertSee('Gráfico de radar por dimensión')
                ->assertSee('Fase alcanzada:');
        }
    }

    public function test_las_mismas_respuestas_dan_el_mismo_puntaje_en_las_dos_rutas(): void
    {
        $puntajes = [];
        foreach ([Ruta::Producto, Ruta::Servicio] as $ruta) {
            $a = $this->crear($ruta);
            $this->responderTodo($a, 2);
            $puntajes[$ruta->value] = $a->resultados()->orderBy('dimension_id')->pluck('puntaje')->all();
        }

        $this->assertSame($puntajes['producto'], $puntajes['servicio']);
    }

    public function test_no_se_puede_responder_una_pregunta_de_la_otra_ruta(): void
    {
        $a = $this->crear(Ruta::Producto);
        $preguntaDeServicio = Pregunta::where('ruta', 'servicio')->firstOrFail();
        $opcion = Opcion::where('pregunta_id', $preguntaDeServicio->id)->firstOrFail();

        $this->put("/autoevaluaciones/{$a->id}", [
            'respuestas' => [$preguntaDeServicio->id => $opcion->id],
            'accion' => 'guardar',
        ])->assertStatus(422);

        $this->assertSame(0, $a->respuestas()->count());
    }

    public function test_no_se_acepta_una_opcion_que_no_es_de_esa_pregunta(): void
    {
        $a = $this->crear(Ruta::Producto);
        [$p1, $p2] = Pregunta::where('ruta', 'producto')->limit(2)->get();
        $opcionDeOtra = Opcion::where('pregunta_id', $p2->id)->firstOrFail();

        $this->put("/autoevaluaciones/{$a->id}", [
            'respuestas' => [$p1->id => $opcionDeOtra->id],
            'accion' => 'guardar',
        ])->assertStatus(422);
    }

    public function test_guardar_avance_conserva_lo_respondido_y_calcula_un_resultado_parcial(): void
    {
        $a = $this->crear(Ruta::Servicio);
        $pregunta = Pregunta::where('ruta', 'servicio')->orderBy('id')->firstOrFail();
        $opcion = Opcion::where('pregunta_id', $pregunta->id)->where('puntaje', 3)->firstOrFail();

        $this->put("/autoevaluaciones/{$a->id}", [
            'respuestas' => [$pregunta->id => $opcion->id], 'accion' => 'guardar',
        ])->assertSessionHas('guardado');

        $this->assertSame('en_curso', $a->fresh()->estado);
        $this->get("/autoevaluaciones/{$a->id}")->assertOk()->assertSee('checked', false);
        $this->assertSame(8, $a->resultados()->count(), 'una fila por dimensión, aunque no tengan respuestas');
        $this->assertSame(1, $a->resultados()->whereNotNull('puntaje')->count(), 'solo una dimensión tiene puntaje');
        $this->assertSame(0, $a->fresh()->fase_alcanzada);
    }

    public function test_no_se_puede_finalizar_con_preguntas_sin_responder(): void
    {
        $a = $this->crear(Ruta::Servicio);

        $this->put("/autoevaluaciones/{$a->id}", ['respuestas' => [], 'accion' => 'finalizar'])
            ->assertSessionHasErrors('respuestas');
        $this->assertSame('en_curso', $a->fresh()->estado);
    }

    public function test_una_autoevaluacion_finalizada_no_se_puede_modificar(): void
    {
        $a = $this->crear(Ruta::Producto);
        $this->responderTodo($a, 1);

        $this->responderTodo($a->fresh(), 3, 'guardar')->assertStatus(409);
    }

    public function test_la_ruta_y_el_nombre_son_obligatorios_y_la_ruta_debe_existir(): void
    {
        $this->post('/autoevaluaciones', ['organizacion' => '', 'ruta' => 'otra'])
            ->assertSessionHasErrors(['organizacion', 'ruta']);
        $this->assertSame(0, Autoevaluacion::count());
    }
}
