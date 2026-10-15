<?php

namespace Tests\Unit;

use App\Instrumento\CalculadoraPuntaje;
use PHPUnit\Framework\TestCase;

class CalculadoraPuntajeTest extends TestCase
{
    /** Dos dimensiones, cuatro capacidades, una por fase. */
    private function capacidades(): array
    {
        return [
            ['id' => 1, 'dimension_id' => 10, 'fase' => 1],
            ['id' => 2, 'dimension_id' => 10, 'fase' => 2],
            ['id' => 3, 'dimension_id' => 20, 'fase' => 3],
            ['id' => 4, 'dimension_id' => 20, 'fase' => 4],
        ];
    }

    private function todasCon(int $puntaje): array
    {
        return array_map(fn ($c) => ['capacidad_id' => $c['id'], 'puntaje' => $puntaje], $this->capacidades());
    }

    public function test_promedia_por_capacidad_y_por_dimension(): void
    {
        $r = (new CalculadoraPuntaje())->calcular($this->capacidades(), [
            ['capacidad_id' => 1, 'puntaje' => 3],
            ['capacidad_id' => 1, 'puntaje' => 2],   // capacidad 1 = 2,5
            ['capacidad_id' => 2, 'puntaje' => 1],   // capacidad 2 = 1
        ]);

        $this->assertSame(2.5, $r['capacidades'][1]);
        $this->assertSame(1.0, $r['capacidades'][2]);
        $this->assertSame(1.75, $r['dimensiones'][10]);
    }

    public function test_sin_respuestas_el_puntaje_es_nulo_y_no_cero(): void
    {
        $r = (new CalculadoraPuntaje())->calcular($this->capacidades(), []);

        $this->assertNull($r['dimensiones'][10]);
        $this->assertNull($r['dimensiones'][20]);
        $this->assertSame(0, $r['fase']);
    }

    public function test_la_fase_exige_el_umbral_en_todas_las_capacidades_hasta_esa_fase(): void
    {
        $calc = new CalculadoraPuntaje();

        $this->assertSame(4, $calc->calcular($this->capacidades(), $this->todasCon(2))['fase']);
        $this->assertSame(0, $calc->calcular($this->capacidades(), $this->todasCon(1))['fase']);

        // Fase 3 completa pero la capacidad de fase 2 queda bajo el umbral: la fase se corta en 1.
        $respuestas = $this->todasCon(3);
        $respuestas[1]['puntaje'] = 1;
        $this->assertSame(1, $calc->calcular($this->capacidades(), $respuestas)['fase']);
    }

    public function test_una_capacidad_sin_responder_corta_la_fase(): void
    {
        $respuestas = array_slice($this->todasCon(3), 0, 3); // falta la capacidad 4 (fase 4)

        $this->assertSame(3, (new CalculadoraPuntaje())->calcular($this->capacidades(), $respuestas)['fase']);
    }
}
