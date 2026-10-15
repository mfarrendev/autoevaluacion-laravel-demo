@php
    $cx = 190; $cy = 190; $radio = 130;
    $vertices = \App\Presentacion\Radar::vertices($valores, $maximo, $radio, $cx, $cy);
    $etiquetas = \App\Presentacion\Radar::vertices(array_fill(0, count($valores), $maximo), $maximo, $radio + 24, $cx, $cy);
@endphp
@extends('layout')
@section('titulo', 'Reporte de '.$autoevaluacion->organizacion)
@section('contenido')
    <h1>Reporte: {{ $autoevaluacion->organizacion }}</h1>
    <p class="suave">
        Ruta {{ $autoevaluacion->ruta->etiqueta() }}.
        @if ($autoevaluacion->estaCompletada()) Autoevaluación finalizada. @else Resultado provisorio: faltan preguntas por responder. @endif
    </p>

    <div class="tarjeta">
        <p><strong>Fase alcanzada:</strong> {{ $autoevaluacion->fase_alcanzada ?: 'ninguna todavía' }} de 4</p>

        <svg viewBox="0 0 380 380" role="img" aria-label="Gráfico de radar por dimensión">
            @foreach ([1, 2, 3] as $anillo)
                <polygon fill="none" stroke="#d9dee5"
                    points="{{ \App\Presentacion\Radar::poligono(\App\Presentacion\Radar::vertices(array_fill(0, count($valores), $anillo), $maximo, $radio, $cx, $cy)) }}"/>
            @endforeach
            @foreach (\App\Presentacion\Radar::vertices(array_fill(0, count($valores), $maximo), $maximo, $radio, $cx, $cy) as [$x, $y])
                <line x1="{{ $cx }}" y1="{{ $cy }}" x2="{{ $x }}" y2="{{ $y }}" stroke="#d9dee5"/>
            @endforeach
            <polygon points="{{ \App\Presentacion\Radar::poligono($vertices) }}" fill="#1f4e8c" fill-opacity="0.25" stroke="#1f4e8c" stroke-width="2"/>
            @foreach ($dimensiones as $i => $dimension)
                <text x="{{ $etiquetas[$i][0] }}" y="{{ $etiquetas[$i][1] }}" font-size="11" text-anchor="middle" fill="#1c2430">{{ $dimension->nombre }}</text>
            @endforeach
        </svg>

        <table>
            <thead><tr><th>Dimensión</th><th>Puntaje (0 a {{ $maximo }})</th></tr></thead>
            <tbody>
            @foreach ($dimensiones as $i => $dimension)
                <tr><td>{{ $dimension->nombre }}</td><td>{{ $valores[$i] === null ? 'sin respuestas' : number_format($valores[$i], 2) }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <p><a href="{{ route('inicio') }}">Nueva autoevaluación</a></p>
@endsection
