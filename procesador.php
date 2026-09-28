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
    'unidades_raw' 
])