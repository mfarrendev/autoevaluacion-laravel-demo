<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Autoevaluación')</title>
    <style>
        :root { --fondo:#f6f7f9; --tarjeta:#fff; --texto:#1c2430; --suave:#5b6573; --linea:#d9dee5; --acento:#1f4e8c; --error:#a32020; }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--fondo); color:var(--texto); font:16px/1.5 system-ui, sans-serif; }
        main { max-width: 760px; margin: 0 auto; padding: 24px 16px 64px; }
        h1 { font-size: 1.5rem; margin: 0 0 4px; }
        h2 { font-size: 1.1rem; margin: 28px 0 8px; }
        p.suave { color: var(--suave); margin-top: 0; }
        .tarjeta { background:var(--tarjeta); border:1px solid var(--linea); border-radius:10px; padding:16px; margin:12px 0; }
        fieldset { border:0; padding:0; margin:0 0 16px; }
        legend { font-weight:600; margin-bottom:6px; }
        label.opcion { display:block; padding:4px 0; }
        input[type=text], select { width:100%; padding:8px; border:1px solid var(--linea); border-radius:6px; font:inherit; }
        button { background:var(--acento); color:#fff; border:0; border-radius:6px; padding:10px 18px; font:inherit; cursor:pointer; }
        button.secundario { background:#fff; color:var(--acento); border:1px solid var(--acento); }
        .error { color:var(--error); } .ok { color:#1d6b3a; }
        table { width:100%; border-collapse:collapse; } td, th { padding:6px 8px; border-bottom:1px solid var(--linea); text-align:left; }
        svg { max-width:100%; height:auto; }
    </style>
</head>
<body>
<main>
    @yield('contenido')
</main>
</body>
</html>
