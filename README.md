# Autoevaluación con dos rutas (Laravel + MySQL)

Proyecto de demostración: un cuestionario de autoevaluación de madurez con **dos rutas de preguntas** (productos y servicios) que alimentan **el mismo algoritmo de puntaje**, y un reporte con gráfico de radar por dimensión.

El objetivo del ejercicio es el problema técnico de fondo: **agregar una ruta nueva a un cuestionario que ya existe sin alterar el algoritmo, sin tocar los resultados ya guardados y con una reversa segura**. Todo el contenido (dimensiones, capacidades, preguntas) es inventado para esta demostración.

> Es un proyecto de demostración creado en octubre de 2026, no un sistema en producción. Se desarrolló con asistencia de IA (Claude Code), y el historial de commits es el real.

Stack: PHP 8.5, Laravel 13, MySQL 8.4 (Docker), PHPUnit.

## Cómo está armado

| Pieza | Dónde | Qué hace |
|---|---|---|
| Algoritmo de puntaje | `app/Instrumento/CalculadoraPuntaje.php` | Función pura. Recibe capacidades y respuestas y devuelve puntajes y fase. **No conoce la ruta.** |
| Servicio de resultados | `app/Instrumento/ServicioDeResultados.php` | Lee respuestas, corre el algoritmo, guarda `resultados`. Solo usa columnas de la línea base |
| Catálogo | `database/data/*.php` + `CargadorDePreguntas` | 8 dimensiones, 16 capacidades (4 por fase), 2 preguntas por capacidad y ruta |
| La bifurcación | columna `ruta` en `preguntas` y `autoevaluaciones` | Decide **qué preguntas se muestran**. Nada más |
| Matriz de trazabilidad | `php artisan matriz:trazabilidad` | Falla si una capacidad queda sin preguntas en alguna ruta; exporta ítem – capacidad – dimensión – fase – regla de puntaje |
| Reporte | `resources/views/reporte.blade.php`, `app/Presentacion/Radar.php` | Radar en SVG, con la geometría separada para poder probarla |

### Reglas de puntaje

- Cada pregunta tiene 4 opciones con puntaje 0 a 3.
- **Capacidad** = promedio de sus preguntas respondidas.
- **Dimensión** = promedio de las capacidades con respuesta (sin respuestas, queda sin puntaje; no vale cero).
- **Fase alcanzada** = la mayor fase *f* tal que todas las capacidades de las fases 1 a *f* tienen puntaje de al menos 2,0.

Como el algoritmo nunca ve la ruta, las mismas respuestas dan el mismo puntaje en las dos rutas (hay una prueba que lo comprueba).

## Cómo se agregó la ruta de servicios

Cuatro migraciones, en orden:

1. `…000001_crear_catalogo` y `…000002_crear_autoevaluaciones`: la **línea base**, el instrumento "que ya existía".
2. `…000003_agregar_ruta`: agrega `ruta` con valor por defecto `'producto'`. Todo lo existente queda en la ruta de producto **sin reescribir ninguna fila**, así que las autoevaluaciones ya respondidas siguen siendo válidas.
3. `…000004_cargar_preguntas_servicios`: inserta las 32 preguntas de servicios y sus opciones, ancladas por código a capacidades existentes. Es idempotente.

### Plan de reversa

`php artisan migrate:rollback --step=2` deja la base como estaba antes. En MySQL un cambio de estructura no se deshace solo si falla a medias, así que la reversa tiene guardias:

- La migración 4 se niega a revertir si ya hay **respuestas** guardadas sobre preguntas de servicios. Borrar respuestas de usuarios nunca es parte de una reversa.
- La migración 3 se niega a quitar la columna `ruta` mientras exista cualquier pregunta o autoevaluación de servicios.

Si una guardia salta, no se toca nada y el mensaje dice qué hacer primero.

## Ejecutar

Requisitos: PHP 8.3 o superior, Composer, Docker.

```bash
docker compose up -d                      # MySQL 8.4 en 127.0.0.1:33060
docker compose exec mysql mysql -uroot -psecret -e \
  "CREATE DATABASE IF NOT EXISTS autoevaluacion_test; GRANT ALL ON autoevaluacion_test.* TO 'app'@'%';"
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed                # instalación nueva
php artisan serve                         # http://localhost:8000
php artisan matriz:trazabilidad --csv=matriz.csv
```

En una base que ya tiene catálogo basta con `php artisan migrate`: la migración 4 carga las preguntas de servicios. En una instalación nueva esa migración no encuentra capacidades todavía y no hace nada, y las carga el seeder.

## Pruebas

```bash
composer test        # usa la base autoevaluacion_test del mismo MySQL
```

25 pruebas. Corren contra **MySQL real, no SQLite**, porque las migraciones y la reversa se comportan distinto según el motor.

| Archivo | Qué comprueba |
|---|---|
| `Unit/CalculadoraPuntajeTest` | Promedios, puntaje nulo (no cero), regla de la fase |
| `Unit/RadarTest` | Geometría del radar, nulos en el centro, valores fuera de rango |
| `Feature/RutasTest` | Cada ruta muestra solo sus preguntas; no se puede responder una pregunta de la otra ruta ni con una opción ajena; guardado parcial; finalizar exige todo; una autoevaluación finalizada no se edita; reporte de ambas rutas |
| `Feature/MatrizTrazabilidadTest` | La matriz pasa, falla si falta cobertura, y el CSV tiene una fila por pregunta |
| `Feature/RegresionMigracionesTest` | Arma la base **antes** de la ruta, crea 12 autoevaluaciones (una a medio responder), migra y compara fila por fila; revierte y compara de nuevo; comprueba las guardias de la reversa y que una instalación nueva con seed quede igual que una migrada |

La prueba de regresión también compara contra números guardados en `tests/fixtures/resultados_linea_base.json`: si alguien cambia el algoritmo, cae aunque "antes" y "después" sigan siendo iguales entre sí. Para aceptar un cambio intencional: `ACTUALIZAR_GOLDEN=1 php artisan test --filter RegresionMigraciones`.

### Cómo se midieron las pruebas

Una prueba que nadie midió no protege nada, así que se pusieron defectos a mano (uno a la vez) y se comprobó que la suite cayera. Primera pasada, 8 defectos, 7 cayeron:

| Defecto puesto | ¿Cae? |
|---|---|
| Umbral de fase 2,0 → 1,5 | Sí |
| Ruta por defecto `servicio` en lugar de `producto` | Sí |
| Preguntas de servicios cargadas como `producto` | Sí |
| Reversa de la migración 4 sin la guardia de respuestas | Sí |
| Reversa de la migración 3 sin la guardia de servicios | Sí |
| Aceptar respuestas a preguntas de otra ruta | Sí |
| La matriz de trazabilidad no falla nunca | Sí |
| Redondear a 1 decimal el puntaje de **capacidad** | No |

El que no cayó es un **mutante equivalente**: con 2 preguntas por capacidad y puntajes enteros, el promedio de una capacidad siempre es múltiplo de 0,5 y redondearlo a un decimal no cambia nada. El mismo defecto en el puntaje de **dimensión** (promedio de dos capacidades, que puede dar 0,25) sí hace caer la suite, aunque lo atrapa la prueba unitaria y no el archivo de números guardados, que no tiene ningún valor con un segundo decimal distinto de cero.

## Límites de esta demostración

- No tiene registro ni inicio de sesión: cualquiera con el enlace ve una autoevaluación.
- El instrumento tiene 16 capacidades y 64 preguntas; uno real tendría bastante más, y sus textos pasarían por validación con usuarios.
- No hay ambiente de QA separado ni integración continua; las pruebas se corren a mano.
- Los datos de prueba son sintéticos y deterministas. No se probó con volumen.
- No se ha medido el rendimiento ni la accesibilidad más allá de lo básico (etiquetas y contraste).
