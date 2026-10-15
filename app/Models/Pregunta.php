<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pregunta extends Model
{
    protected $table = 'preguntas';
    public $timestamps = false;
    protected $guarded = [];

    public function capacidad(): BelongsTo
    {
        return $this->belongsTo(Capacidad::class);
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(Opcion::class)->orderBy('orden');
    }
}
