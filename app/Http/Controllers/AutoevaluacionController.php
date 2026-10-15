<?php

namespace App\Http\Controllers;

use App\Enums\Ruta;
use App\Instrumento\EscalaRespuesta;
use App\Instrumento\ServicioDeResultados;
use App\Models\Autoevaluacion;
use App\Models\Dimension;
use App\Models\Opcion;
use App\Models\Pregunta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AutoevaluacionController extends Controller
{
    public function __construct(private ServicioDeResultados $resultados)
    {
    }

    public function inicio(): View
    {
        return view('inicio', ['rutas' => Ruta::cases()]);
    }

    public function crear(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'organizacion' => ['required', 'string', 'max:120'],
            'ruta' => ['required', Rule::enum(Ruta::class)],
        ]);

        $autoevaluacion = Autoevaluacion::create($datos);

        return redirect()->route('autoevaluaciones.mostrar', $autoevaluacion);
    }

    public function mostrar(Autoevaluacion $autoevaluacion): View|RedirectResponse
    {
        if ($autoevaluacion->estaCompletada()) {
            return redirect()->route('autoevaluaciones.reporte', $autoevaluacion);
        }

        $preguntas = Pregunta::with(['opciones', 'capacidad.dimension'])
            ->where('ruta', $autoevaluacion->ruta->value)
            ->orderBy('capacidad_id')->orderBy('orden')
            ->get()
            ->groupBy(fn ($p) => $p->capacidad->dimension->nombre);

        return view('autoevaluacion', [
            'autoevaluacion' => $autoevaluacion,
            'preguntasPorDimension' => $preguntas,
            'respondidas' => $autoevaluacion->respuestas()->pluck('opcion_id', 'pregunta_id'),
        ]);
    }

    public function guardar(Request $request, Autoevaluacion $autoevaluacion): RedirectResponse
    {
        abort_if($autoevaluacion->estaCompletada(), 409, 'La autoevaluación ya fue finalizada.');

        $esDeEstaRuta = Pregunta::where('ruta', $autoevaluacion->ruta->value)->pluck('id')->all();
        $enviadas = (array) $request->input('respuestas', []);

        // Solo se aceptan preguntas de la ruta de esta autoevaluación, con una
        // opción que pertenezca a esa pregunta. Lo demás se rechaza entero.
        foreach ($enviadas as $preguntaId => $opcionId) {
            $valida = in_array((int) $preguntaId, $esDeEstaRuta, true)
                && Opcion::where('id', $opcionId)->where('pregunta_id', $preguntaId)->exists();
            abort_unless($valida, 422, 'Respuesta inválida.');
        }

        DB::transaction(function () use ($autoevaluacion, $enviadas) {
            foreach ($enviadas as $preguntaId => $opcionId) {
                $autoevaluacion->respuestas()->updateOrCreate(
                    ['pregunta_id' => $preguntaId],
                    ['opcion_id' => $opcionId],
                );
            }
        });

        if ($request->input('accion') === 'finalizar') {
            $faltan = count($esDeEstaRuta) - $autoevaluacion->respuestas()->count();
            if ($faltan > 0) {
                return back()->withErrors(['respuestas' => "Faltan {$faltan} preguntas por responder."]);
            }
            $autoevaluacion->update(['estado' => 'completada']);
        }

        $this->resultados->recalcular($autoevaluacion->id);

        return $request->input('accion') === 'finalizar'
            ? redirect()->route('autoevaluaciones.reporte', $autoevaluacion)
            : back()->with('guardado', 'Avance guardado.');
    }

    public function reporte(Autoevaluacion $autoevaluacion): View
    {
        $dimensiones = Dimension::orderBy('orden')->get();
        $puntajes = $autoevaluacion->resultados()->pluck('puntaje', 'dimension_id');

        return view('reporte', [
            'autoevaluacion' => $autoevaluacion,
            'dimensiones' => $dimensiones,
            'valores' => $dimensiones->map(fn ($d) => isset($puntajes[$d->id]) ? (float) $puntajes[$d->id] : null)->all(),
            'maximo' => EscalaRespuesta::PUNTAJE_MAXIMO,
        ]);
    }
}
