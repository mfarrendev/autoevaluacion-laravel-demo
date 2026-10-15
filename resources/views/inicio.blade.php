@extends('layout')
@section('titulo', 'Nueva autoevaluación')
@section('contenido')
    <h1>Autoevaluación de madurez</h1>
    <p class="suave">Demostración: un cuestionario con dos rutas de preguntas que alimentan un mismo algoritmo de puntaje.</p>

    <form class="tarjeta" method="POST" action="{{ route('autoevaluaciones.crear') }}">
        @csrf
        <fieldset>
            <legend><label for="organizacion">Nombre de la organización</label></legend>
            <input id="organizacion" type="text" name="organizacion" value="{{ old('organizacion') }}" required maxlength="120">
            @error('organizacion')<p class="error">{{ $message }}</p>@enderror
        </fieldset>
        <fieldset>
            <legend><label for="ruta">¿Qué ofrece su organización?</label></legend>
            <select id="ruta" name="ruta" required>
                @foreach ($rutas as $ruta)
                    <option value="{{ $ruta->value }}" @selected(old('ruta') === $ruta->value)>{{ $ruta->etiqueta() }}</option>
                @endforeach
            </select>
            @error('ruta')<p class="error">{{ $message }}</p>@enderror
        </fieldset>
        <button type="submit">Comenzar</button>
    </form>
@endsection
