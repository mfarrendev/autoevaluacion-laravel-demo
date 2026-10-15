<?php

namespace Tests\Unit;

use App\Presentacion\Radar;
use PHPUnit\Framework\TestCase;

class RadarTest extends TestCase
{
    public function test_el_primer_eje_apunta_hacia_arriba(): void
    {
        [$x, $y] = Radar::vertices([3.0, 3.0, 3.0, 3.0], 3.0, 100.0, 200.0, 200.0)[0];

        $this->assertSame(200.0, $x);
        $this->assertSame(100.0, $y);
    }

    public function test_un_puntaje_nulo_se_dibuja_en_el_centro_y_no_se_omite(): void
    {
        $vertices = Radar::vertices([null, 3.0, 0.0], 3.0, 100.0, 50.0, 50.0);

        $this->assertCount(3, $vertices);
        $this->assertSame([50.0, 50.0], $vertices[0]);
    }

    public function test_un_puntaje_fuera_de_rango_no_se_sale_del_grafico(): void
    {
        [$x, $y] = Radar::vertices([9.0], 3.0, 100.0, 0.0, 0.0)[0];

        $this->assertSame(0.0, $x);
        $this->assertSame(-100.0, $y);
    }

    public function test_el_poligono_une_los_vertices(): void
    {
        $this->assertSame('1,2 3,4', Radar::poligono([[1.0, 2.0], [3.0, 4.0]]));
    }
}
