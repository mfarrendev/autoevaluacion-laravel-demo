<?php

/*
 * Preguntas de la ruta de servicios: dos por capacidad, con lenguaje
 * simplificado. La clave es el código de la capacidad del modelo vigente; el
 * modelo (dimensiones, capacidades, fases) no cambia, solo se agrega una
 * segunda fuente de información para cada capacidad.
 *
 * Esta lista la carga la migración 2026_10_15_000004 y la seguridad de la
 * reversa depende de que los códigos (CAP-S1, CAP-S2) no se reutilicen.
 */

return [
    'EQP-1' => [
        '¿Cuántas personas de su equipo trabajan hoy en el servicio con tiempo suficiente para atender a sus clientes?',
        '¿Las personas clave se han comprometido a seguir en el equipo mientras el servicio crece?',
    ],
    'EQP-2' => [
        '¿Cada integrante sabe qué parte del servicio le corresponde entregar?',
        '¿El equipo se reúne con regularidad para revisar los casos atendidos y deja acuerdos por escrito?',
    ],
    'PIN-1' => [
        '¿Se ha identificado qué parte de su método, plataforma o código puede protegerse o licenciarse?',
        '¿Está claro de quién son los datos, los modelos y los materiales que usa para entregar el servicio?',
    ],
    'PIN-2' => [
        '¿Tiene definido cómo protegerá su método y su software frente a copias, por ejemplo con contratos o registros?',
        '¿Revisó que las herramientas de terceros que usa permitan venderlas como parte de su servicio?',
    ],
    'MER-1' => [
        '¿Sabe qué tipo de organizaciones contratan su servicio y cuánto pagan por él?',
        '¿Ha comparado su servicio con las alternativas que hoy usan sus clientes, con datos concretos?',
    ],
    'MER-2' => [
        '¿Ha conversado con posibles clientes para confirmar que el problema que resuelve su servicio existe?',
        '¿Anotó lo que le dijeron y sacó conclusiones por escrito?',
    ],
    'TEC-1' => [
        '¿Su servicio ya se entrega a clientes reales y funciona sin que el equipo fundador tenga que intervenir cada vez?',
        '¿Tiene un plan para que el servicio pueda repetirse con más clientes sin rehacerlo desde cero?',
    ],
    'TEC-2' => [
        '¿Realizó un piloto que muestre que su servicio produce el resultado prometido?',
        '¿El piloto quedó documentado de modo que otra persona del equipo pueda repetirlo?',
    ],
    'FIN-1' => [
        '¿Conoce los fondos o contratos a los que podría postular para financiar el servicio?',
        '¿Tiene ingresos o financiamiento asegurados para los próximos doce meses?',
    ],
    'FIN-2' => [
        '¿Tiene una proyección de ingresos y costos del servicio a tres años?',
        '¿Calculó cuánto dinero necesita para llegar a un nivel de clientes que cubra sus costos?',
    ],
    'REG-1' => [
        '¿Identificó las normas, permisos o contratos que su servicio necesita para operar con ciertos clientes?',
        '¿Evaluó los riesgos de privacidad y seguridad de los datos que maneja para sus clientes?',
    ],
    'REG-2' => [
        '¿Ha iniciado o completado certificaciones o auditorías que sus clientes le exigen?',
        '¿Hay una persona a cargo de mantener el cumplimiento de esas exigencias?',
    ],
    'ALI-1' => [
        '¿Tiene acuerdos con socios que le den acceso a clientes o complementen su servicio?',
        '¿Esos acuerdos están firmados?',
    ],
    'ALI-2' => [
        '¿Su equipo participa en redes, programas o comunidades del ecosistema de ciencia y tecnología?',
        '¿Cuenta con mentores o asesores externos con experiencia en el mercado de servicios?',
    ],
    'OPE-1' => [
        '¿Definió cómo atenderá a muchos más clientes sin que la calidad baje?',
        '¿Mide la calidad del servicio y el costo de atender a cada cliente?',
    ],
    'OPE-2' => [
        '¿Están descritos los pasos con que se entrega el servicio de principio a fin?',
        '¿Lleva registro de las herramientas y proveedores de los que depende su servicio?',
    ],
];
