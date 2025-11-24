<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maestro de Configuración de Líneas de Producción (MES v2)
    |--------------------------------------------------------------------------
    | Define los inputs específicos para cada línea.
    | Tipos: 'number', 'text', 'time', 'date', 'select', 'header'.
    |
    */

    // =========================================================================
    // LÍNEA A (Pastas Cortas) - Basado en LineaAController
    // =========================================================================
    'linea-a' => [
        'label' => 'Línea A',
        'fields' => [
            ['type' => 'header', 'label' => 'Humedades de Proceso'],
            ['name' => 'trabatto', 'label' => '% Trabatto', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'presecado', 'label' => '% Presecado', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'final', 'label' => '% Final', 'type' => 'number', 'step' => '0.01'],

            ['type' => 'header', 'label' => 'Parámetros de Extrusión'],
            ['name' => 'semola', 'label' => 'Carga Sémola (Kg/h)', 'type' => 'number'],
            ['name' => 'porcentaje_agua', 'label' => '% Agua', 'type' => 'number'],
            ['name' => 'pre_tornillo_iz', 'label' => 'Presión Tornillo Izq (Bar)', 'type' => 'number'],
            ['name' => 'pre_tornillo_der', 'label' => 'Presión Tornillo Der (Bar)', 'type' => 'number'],
            ['name' => 'vacio_min', 'label' => 'Vacío (Min)', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Temperaturas (°C)'],
            ['name' => 'temperatura_trabatto', 'label' => 'Temp. Trabatto', 'type' => 'number'],
            ['name' => 'temperatura_presecado', 'label' => 'Temp. Presecado', 'type' => 'number'],
            ['name' => 'delta_presecado', 'label' => 'Delta Presecado', 'type' => 'number'],
            ['name' => 'temperatura_secado', 'label' => 'Temp. Secado', 'type' => 'number'],
            ['name' => 'delta_secado', 'label' => 'Delta Secado', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Dosificación'],
            ['name' => 'porcentaje_dosificacion', 'label' => '% Dosificación', 'type' => 'number'],
            ['name' => 'porsentaje_remolida', 'label' => '% Remolida', 'type' => 'number'],
        ]
    ],

    // =========================================================================
    // LÍNEA B (Pastas Largas) - Basado en LineaBController
    // =========================================================================
    'linea-b' => [
        'label' => 'Línea B',
        'fields' => [
            ['type' => 'header', 'label' => 'Temperaturas de Secado'],
            ['name' => 'temp_aerotermo', 'label' => 'Temp. Aerotermo', 'type' => 'number'],
            ['name' => 'delta_aerotermo', 'label' => 'Delta Aerotermo', 'type' => 'number'],
            ['name' => 'presecado', 'label' => 'Presecado', 'type' => 'number'], // ¿Es % o Temp? Asumo valor numérico
            
            ['name' => 'temp_1a_presecado', 'label' => 'Temp. 1a Presecado', 'type' => 'number'],
            ['name' => 'delta_1a_presecado', 'label' => 'Delta 1a Presecado', 'type' => 'number'],
            ['name' => 'temp_2a_presecado', 'label' => 'Temp. 2a Presecado', 'type' => 'number'],
            ['name' => 'delta_2a_presecado', 'label' => 'Delta 2a Presecado', 'type' => 'number'],
            
            ['name' => 'temp_1a_gpl', 'label' => 'Temp. 1a GPL', 'type' => 'number'],
            ['name' => 'delta_1a_gpl', 'label' => 'Delta 1a GPL', 'type' => 'number'],
            ['name' => 'temp_2a_gpl', 'label' => 'Temp. 2a GPL', 'type' => 'number'],
            ['name' => 'delta_2a_gpl', 'label' => 'Delta 2a GPL', 'type' => 'number'],
            
            ['name' => 'temperatura_enfriador', 'label' => 'Temp. Enfriador', 'type' => 'number'],
            ['name' => 'salida_enfriador', 'label' => 'Salida Enfriador', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Parámetros Mecánicos'],
            ['name' => 'pre_tornillo', 'label' => 'Presión Tornillo', 'type' => 'number'],
            ['name' => 'vacio_min', 'label' => 'Vacío Min', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Checklist Limpieza'],
            ['name' => 'limpieza_filtros_vacio', 'label' => 'Limp. Filtros Vacío', 'type' => 'select', 'options' => ['Si', 'No']],
            ['name' => 'limpieza_magneto', 'label' => 'Limp. Magneto', 'type' => 'select', 'options' => ['Si', 'No']],

            ['type' => 'header', 'label' => 'Aditivos e Ingredientes'],
            ['name' => 'nombre_lot_ingrediente', 'label' => 'Lote Ingrediente', 'type' => 'text'],
            ['name' => 'porsentaje_dosificacion_ingrediente', 'label' => '% Dosif. Ingrediente', 'type' => 'number'],
            ['name' => 'porcentaje_remolida', 'label' => '% Remolida', 'type' => 'number'],
            ['name' => 'vencimiento', 'label' => 'F. Vencimiento', 'type' => 'date'],
        ]
    ],

    // =========================================================================
    // LÍNEA C (Especialidades/Nidos/Lasagna) - Basado en LineaCController
    // =========================================================================
    'linea-c' => [
        'label' => 'Línea C',
        'fields' => [
            ['type' => 'header', 'label' => 'Flujos y Mezcla'],
            ['name' => 'KG_semolato_semola', 'label' => 'Kg Sémola/Semolato', 'type' => 'number'],
            ['name' => 'porcentaje_agua', 'label' => '% Agua', 'type' => 'number'],
            ['name' => 'liquido', 'label' => 'Líquido', 'type' => 'number'],
            ['name' => 'flujometro_agua', 'label' => 'Flujómetro Agua', 'type' => 'number'],
            ['name' => 'flujometro_liquido', 'label' => 'Flujómetro Líquido', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Temperaturas y Presiones'],
            ['name' => 'temp_tornillo', 'label' => 'Temp. Tornillo', 'type' => 'number'],
            ['name' => 'temp_cabezal', 'label' => 'Temp. Cabezal', 'type' => 'number'],
            ['name' => 'pre_tornillo', 'label' => 'Presión Tornillo', 'type' => 'number'],
            ['name' => 'presion_vacio', 'label' => 'Presión Vacío', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Pasteurización'],
            ['name' => 'temperatura_pasteurizacion', 'label' => 'Temp. Pasteurización', 'type' => 'number'],
            ['name' => 'tiempo_exposicion_pasteurizacion', 'label' => 'Tiempo Expo. (min)', 'type' => 'number'],
            ['name' => 'presion_pausterizador_arriba', 'label' => 'Presión Arriba', 'type' => 'number'],
            ['name' => 'presion_pausterizador_abajo', 'label' => 'Presión Abajo', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Parámetros de Corte (Lasagna/Nidos)'],
            ['name' => 'estirador', 'label' => 'Estirador', 'type' => 'number'],
            ['name' => 'cortapasta', 'label' => 'Cortapasta', 'type' => 'number'],
            ['name' => 'cortachasquido', 'label' => 'Cortachasquido', 'type' => 'number'],
            ['name' => 'largo_lasagna', 'label' => 'Largo Lasagna', 'type' => 'number'],
            ['name' => 'numero_lasagna', 'label' => 'No. Lasagna', 'type' => 'number'],
            ['name' => 'largo_nidos', 'label' => 'Largo Nidos', 'type' => 'number'],
            ['name' => 'numero_nidos', 'label' => 'No. Nidos', 'type' => 'number'],
            ['name' => 'diametro_producto', 'label' => 'Diámetro', 'type' => 'number'],
            ['name' => 'espesor_producto_seco', 'label' => 'Espesor Seco', 'type' => 'number'],
            ['name' => 'Esp_ex_nidos', 'label' => 'Espesor Nidos', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Velocidades y Ventilaciones'],
            ['name' => 'vel_calibrador', 'label' => 'Vel. Calibrador', 'type' => 'number'],
            ['name' => 'vel_cinta', 'label' => 'Vel. Cinta', 'type' => 'number'],
            ['name' => 'vel_rodillo', 'label' => 'Vel. Rodillo', 'type' => 'number'],
            ['name' => 'vel_rizador', 'label' => 'Vel. Rizador', 'type' => 'number'],
            ['name' => 'vel_bastidores', 'label' => 'Vel. Bastidores', 'type' => 'number'],
            ['name' => 'ventilacion_deshoja', 'label' => 'Vent. Deshoja', 'type' => 'number'],
            ['name' => 'ventilacion_final_linea', 'label' => 'Vent. Final', 'type' => 'number'],
            ['name' => 'velocidad_salto_telares', 'label' => 'Vel. Salto Telares', 'type' => 'number'],
            ['name' => 'salto_telares', 'label' => 'Salto Telares', 'type' => 'number'],
            
            ['type' => 'header', 'label' => 'Soplado'],
            ['name' => 'retraso_soplo', 'label' => 'Retraso Soplo', 'type' => 'number'],
            ['name' => 'durada_soplo', 'label' => 'Duración Soplo', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Otros'],
            ['name' => 'cant_laminas_bastidor', 'label' => 'Láminas/Bastidor', 'type' => 'number'],
            ['name' => 'numero_bastidores_carro', 'label' => 'Bastidores/Carro', 'type' => 'number'],
            ['name' => 'limp_filtro_vacio', 'label' => 'Limp. Filtro Vacío', 'type' => 'select', 'options' => ['Si', 'No']],
            ['name' => 'nombre_lote_ingrediente', 'label' => 'Lote Ingrediente', 'type' => 'text'],
            ['name' => 'porsentaje_dosificacion_ingrediente', 'label' => '% Dosif. Ingrediente', 'type' => 'number'],
        ]
    ],

    // =========================================================================
    // LÍNEA D (Pastas Cortas) - Basado en LineaDController
    // =========================================================================
    'linea-d' => [
        'label' => 'Línea D',
        'fields' => [
            ['type' => 'header', 'label' => 'Humedades (%)'],
            ['name' => 'trabatto', 'label' => 'Trabatto', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'presecado', 'label' => 'Presecado', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'secado', 'label' => 'Secado', 'type' => 'number', 'step' => '0.01'],
            ['name' => 'final', 'label' => 'Final', 'type' => 'number', 'step' => '0.01'],

            ['type' => 'header', 'label' => 'Parámetros de Proceso'],
            ['name' => 'semola', 'label' => 'Sémola (Kg/h)', 'type' => 'number'],
            ['name' => 'porcentaje_agua', 'label' => '% Agua', 'type' => 'number'],
            ['name' => 'litros_agua', 'label' => 'Litros Agua (Lt/h)', 'type' => 'number'],
            ['name' => 'rpm_tornillos', 'label' => 'RPM Tornillos', 'type' => 'number'],
            ['name' => 'velocidad_cuchillas', 'label' => 'Vel. Cuchillas', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Temperaturas (°C)'],
            ['name' => 'temperatura_trabatto', 'label' => 'Temp. Trabatto', 'type' => 'number'],
            ['name' => 'temperatura_presecado', 'label' => 'Temp. Presecado', 'type' => 'number'],
            ['name' => 'delta_presecado', 'label' => 'Delta Presecado', 'type' => 'number'],
            ['name' => 'temperatura_secado', 'label' => 'Temp. Secado', 'type' => 'number'],
            ['name' => 'delta_secado', 'label' => 'Delta Secado', 'type' => 'number'],
            ['name' => 'temperatura_enfriador', 'label' => 'Temp. Enfriador', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Presiones y Vacío'],
            ['name' => 'pre_tornillo_iz', 'label' => 'Presión Tornillo Izq', 'type' => 'number'],
            ['name' => 'pre_tornillo_der', 'label' => 'Presión Tornillo Der', 'type' => 'number'],
            ['name' => 'vacio_min', 'label' => 'Vacío', 'type' => 'number'],

            ['type' => 'header', 'label' => 'Dosificación'],
            ['name' => 'porcentaje_dosificacion', 'label' => '% Dosificación', 'type' => 'number'],
            ['name' => 'porsentaje_remolida', 'label' => '% Remolida', 'type' => 'number'],
        ]
    ],
];