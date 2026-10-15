<?php

namespace App\Models;

use App\Enums\Ruta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Autoevaluacion extends Model
{
    protected $table = 'autoevaluaciones';
    protected $guarded = [];
    protected $casts = ['ruta' => Ruta::class];

    public function respuestas(): HasMany
    {
        return $this->hasMany(Respuesta::class);
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(Resultado::class);
    }

    public function estaCompletada(): bool
    {
        return $this->estado === 'completada';
    }
}
