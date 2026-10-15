<?php

namespace App\Instrumento;

/** Las cuatro opciones de respuesta de cada pregunta, con su puntaje (0 a 3). */
final class EscalaRespuesta
{
    public const OPCIONES = [
        0 => 'No lo hemos considerado',
        1 => 'Lo estamos explorando',
        2 => 'Lo tenemos definido en parte',
        3 => 'Lo tenemos consolidado y documentado',
    ];

    public const PUNTAJE_MAXIMO = 3;
}
