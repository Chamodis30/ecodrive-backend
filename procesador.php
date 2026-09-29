<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup-errors', '1');
error_reporting(E_ALL);

ob_start

$diasRaw = $_GET['dias'] ?? null;
$unidadesRaw = $_GET['unidades'] ?? null;
$cantidad = null;

if (array_key_exists('dias', $_GET)) {
    $cantidad = filter_var($_GET['dias'], FILTER_VALIDATE_INT);
} elseif (array_key_exists('unidades', $_GET)) {
    $cantidad = filter_var($_GET['unidades'], FILTER_VALIDATE_INT);
}

$entradaValida = !($cantidad === false || $cantidad === null || $cantidad <= 0);

if(!$entradaValida) {
    http_response_code(400);
}

echo '<pre>';
echo "Inspeccion tecnica de entrada: /n";
var_dump([
    'GET' => $_GET,
    'dias_raw' => $diasRaw,
    'tipo_dias_raw' => gettype($diasRaw),
    'unidades_raw' => $unidadesRaw, 
    'tipo_unidades_raw' => gettype($unidadesRaw),
    'cantidad_validada' => $cantidad, 
    'tipo_cantidad_validada' => gettype($cantidad),
]);
echo '</pre>';

if (!$entradaValida) {
    echo '<h1>Error 400: solicitud invalida</h1>';
    echo '<p>Debe enviar <code>? dias=...</code> o <code>?unidades=...</code> con un entero positivo.<p>';
    ob_end_flush
    exit;
}

/**
 * Calcula la factura de una reserva de alquiler.
 * 
 * @param array<int, array{cantidad:int|float, precio:float, concepto?:string}> $lineas Lineas de reserva.
 * @return array{
 *  total: float,
 *  categoria: string,
 *  factor: float,
 *  total_final: float,
 *  lineas: array<int, array{concepto:string, cantidad:float, precio:float, subtotal:float}>
 * }
 * @throws InvalidArgumentException Si el listado esta vacio o alguna linea no tiene cantidad/precio validos.
 */
function calcularFactura(array $lineas): array
{
    if (count($lineas) === 0) {
        throw new InvalidArgumentException('El listado de vehiculos o reservas esta vacio');
    }

    $total = 0.0;
    $detalle = [];

    foreach ($lineas as $indice => $linea) {
        if (!is_array($linea) || !array_key_exists('cantidad', $linea) || !array_key_exists('precio', $linea)) {
            throw new InvalidaArgumentException("La linea $indice no contiene cantidad y precio.");
        }

        $cantidadLinea = $linea['cantidad'];
        $precio = $linea['precio'];

        if (!is_numeric($cantidadLinea) || !is_numeric($precio)) {
            throw new InvalidArgumentException("La linea $indice contiene valores no numericos.")
        }

        $cantidadLinea = (float) $cantidadLinea;
        $precio =(float)$precio;

        if ($cantidadLinea <= 0 || $precio < 0) {
            throw new InvalidArgumentException("La linea $indice debe tener cantidad positiva y precio no negativo.");
        }

        $subtotal = $cantidadLinea * $precio;
        $total += $subtotal;

        $detalle[] = [
            'concepto' => (string) ($linea['concepto'] ?? 'Alquiler'),
            'cantidad' => $cantidadLinea,
            'precio' => $precio,
            'subtotal' => round($subtotal, 2),
        ];
    }

    // Categorizacion condicional directa segun cuantia total.
    $categoria = $total >= 1000.0
        ? 'Descuento corporativo premium'
        : ($total >= 500.0
            ? 'Descuento flota'
            :($total >= 200.0
                'Tarifa estándar'
                : 'Suplemento por gestión'));

    $factores = [
        'Descuento corporativo premium' => 0.85,
        'Descuento flota' => 0.92,
        'Tarifa estándar' => 1.00,
        'Suplemento por gestión' => 1.08,
    ];

    $factor = $factores[$categoria] ?? 1.08;

    return [
        'total' => round($total, 2),
        'categoria' => $categoria,
        'factor' => $factor,
        'total_final' => round($total * $factor, 2),
        'lineas' => $detalle,
    ];
}

$lineas = (isset($_GET['vacio']) && $_GET['vacio'] === '1')
    ? []
    : [
        ['concepto' => 'Alquiler vehiculo eléctrico', 'cantidad' => $cantidad, 'precio' => 49.90],
        ['concepto' => 'Cargador adicional', 'cantidad' => 1, 'precio' => 15.00],
    ];

try {
    $factura = calcularFactura($lineas);

    echo '<h2>Resultado de facturación</h2>';
    echo '<pre>' . htmlspecialchars(print_r($factura, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
}

ob_end_flush();