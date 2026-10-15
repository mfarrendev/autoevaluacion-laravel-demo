@extends('layout')
@section('titulo', 'Autoevaluación de '.$autoevaluacion->organizacion)
@section('contenido')
    <h1>{{ $autoevaluacion->organizacion }}</h1>
    <p class="suave">Ruta: {{ $autoevaluacion->ruta->etiqueta() }}. Puede guardar el avance y volver más tarde.</p>

    @if (session('guardado'))<p class="ok">{{ session('guardado') }}</p>@endif
    @error('respuestas')<p class="error">{{ $message }}</p>@enderror

    <form method="POST" action="{{ route('autoevaluaciones.guardar', $autoevaluacion) }}">
        @csrf
        @method('PUT')

        @foreach ($preguntasPorDimension as $dimension => $preguntas)
            <h2>{{ $dimension }}</h2>
            @foreach ($preguntas as $pregunta)
                <fieldset class="tarjeta">
                    <legend>{{ $pregunta->texto }}</legend>
                    @foreach ($pregunta->opciones as $opcion)
                        <label class="opcion">
                            <input type="radio" name="respuestas[{{ $pregunta->id }}]" value="{{ $opcion->id }}"
                                @checked(($respondidas[$pregunta->id] ?? null) === $opcion->id)>
                            {{ $opcion->texto }}
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
        @endforeach

        <button class="secundario" type="submit" name="accion" value="guardar">Guardar avance</button>
        <button type="submit" name="accion" value="finalizar">Finalizar y ver reporte</button>
    </form>
@endsection
