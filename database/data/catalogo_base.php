<?php

/*
 * Instrumento de ejemplo: la "línea base" que ya estaría en producción.
 * Ocho dimensiones, dieciséis capacidades (cuatro por fase) y dos preguntas
 * por capacidad, escritas para empresas que venden un producto.
 *
 * Todo el contenido es inventado para esta demostración.
 *
 * Las preguntas de la ruta de servicios viven aparte, en
 * preguntas_servicios.php, porque las carga una migración.
 */

return [
    'dimensiones' => [
        'EQP' => 'Equipo',
        'PIN' => 'Propiedad intelectual',
        'MER' => 'Mercado',
        'TEC' => 'Tecnología',
        'FIN' => 'Financiamiento',
        'REG' => 'Regulación',
        'ALI' => 'Alianzas',
        'OPE' => 'Operación',
    ],

    // codigo => [dimensión, fase, nombre, [pregunta 1, pregunta 2]]
    'capacidades' => [
        'EQP-1' => ['EQP', 1, 'Dedicación del equipo', [
            '¿Qué parte del equipo fundador trabaja en el proyecto con dedicación suficiente para avanzar el desarrollo del producto?',
            '¿Existe un compromiso formal de permanencia de las personas clave durante el desarrollo?',
        ]],
        'EQP-2' => ['EQP', 3, 'Roles y gestión del equipo', [
            '¿Están definidos los roles y responsabilidades de cada integrante del equipo?',
            '¿Se revisan las metas del equipo con una frecuencia fija y queda registro de las decisiones?',
        ]],
        'PIN-1' => ['PIN', 2, 'Identificación de activos protegibles', [
            '¿Se han identificado qué resultados del desarrollo pueden protegerse (patentes, software, secretos)?',
            '¿Está claro quién es dueño de cada resultado entre la institución de origen y el equipo?',
        ]],
        'PIN-2' => ['PIN', 4, 'Estrategia de protección', [
            '¿Existe una estrategia de protección con plazos y países definidos para el producto?',
            '¿Se ha revisado que el producto pueda comercializarse sin infringir derechos de terceros?',
        ]],
        'MER-1' => ['MER', 3, 'Conocimiento del cliente y del problema', [
            '¿Se conoce con datos quién compra el producto y cuánto está dispuesto a pagar?',
            '¿Se han dimensionado el mercado objetivo y sus competidores directos con fuentes verificables?',
        ]],
        'MER-2' => ['MER', 1, 'Primeras conversaciones con clientes', [
            '¿Se ha conversado con posibles usuarios para confirmar que el problema existe?',
            '¿Se registraron las conversaciones y se extrajeron conclusiones por escrito?',
        ]],
        'TEC-1' => ['TEC', 4, 'Madurez de la solución', [
            '¿El producto ha sido probado en un entorno real y no solo en laboratorio?',
            '¿Existe un plan para llevar el prototipo a un producto que se pueda fabricar o desplegar de forma repetible?',
        ]],
        'TEC-2' => ['TEC', 2, 'Prueba de concepto', [
            '¿Existe una prueba de concepto que demuestre que la tecnología cumple la función principal?',
            '¿Los resultados de la prueba están documentados de forma que otra persona pueda repetirlos?',
        ]],
        'FIN-1' => ['FIN', 1, 'Fuentes de financiamiento iniciales', [
            '¿Se conocen los fondos públicos o privados a los que el proyecto puede postular?',
            '¿Hay financiamiento comprometido para los próximos doce meses?',
        ]],
        'FIN-2' => ['FIN', 3, 'Plan financiero', [
            '¿Existe una proyección de ingresos y costos del producto a tres años?',
            '¿Se ha calculado cuánto capital se necesita para llegar a las primeras ventas?',
        ]],
        'REG-1' => ['REG', 2, 'Requisitos regulatorios y éticos', [
            '¿Se identificaron las normas y autorizaciones que el producto necesita para salir al mercado?',
            '¿Se evaluaron los aspectos éticos y de seguridad de las pruebas con personas o con datos sensibles?',
        ]],
        'REG-2' => ['REG', 4, 'Cumplimiento y certificaciones', [
            '¿Se han iniciado o completado las certificaciones exigidas para vender el producto?',
            '¿Hay una persona responsable de mantener el cumplimiento normativo?',
        ]],
        'ALI-1' => ['ALI', 3, 'Alianzas estratégicas', [
            '¿Existen acuerdos con socios que aporten fabricación, distribución o acceso a clientes?',
            '¿Los acuerdos con socios están formalizados por escrito?',
        ]],
        'ALI-2' => ['ALI', 1, 'Vínculo con el ecosistema', [
            '¿El equipo participa en redes, programas o comunidades de emprendimiento científico?',
            '¿Cuenta con mentores o asesores externos que aporten experiencia en la industria?',
        ]],
        'OPE-1' => ['OPE', 4, 'Operación a escala', [
            '¿Se ha definido cómo se producirá y entregará el producto cuando las ventas crezcan?',
            '¿Existen indicadores de calidad y de costo por unidad producida?',
        ]],
        'OPE-2' => ['OPE', 2, 'Procesos básicos', [
            '¿Están descritos los procesos básicos para fabricar o preparar el producto?',
            '¿Se lleva registro de los insumos y proveedores críticos?',
        ]],
    ],
];
