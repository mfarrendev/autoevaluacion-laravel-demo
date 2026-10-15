<?php

namespace App\Enums;

/**
 * Por qué camino responde una organización: el del producto (la línea base)
 * o el de servicios. La ruta solo decide QUÉ preguntas se muestran; el
 * algoritmo de puntaje nunca la ve.
 */
enum Ruta: string
{
    case Producto = 'producto';
    case Servicio = 'servicio';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Producto => 'Productos',
            self::Servicio => 'Servicios',
        };
    }
}
