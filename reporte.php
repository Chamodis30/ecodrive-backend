<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Catalogo de la flota
$flota = [
    [
        'modelo' => 'Citroën ë-C4',
        'categoria' => 'sedán eléctrico',
        'autonomia_km' => 420,
        'unidades_disponibles' => 8,
        'descuento' => null,
        'extras' => ['cargador portátil', 'GPS'],
    ],
    [
        'modelo' => 'Peugeot e-208',
        'categoria' => 'compacto urbano',
        'autonomia_km' => 362,
        'unidades_disponibles' => 12,
        'descuento' => 5.5,
        // Sin clave extras
    ],
    [
        'modelo' => 'Renault Zoe',
        'categoria' => 'utilitario eléctrico',
        'autonomia_km' => 395,
        'unidades_disponibles' => 5,
        'descuento' => null,
        'extras' => [],
    ],
    [
        'modelo' => 'Tesla Model 3',
        'categoria' => 'berlina premium',
        'autonomia_km' => 510,
        'unidades_disponibles' => 3,
        'descuento' => 8.0,
        'extras' => ['Autopilot', 'Tejado panorámico'],
    ],
];

// Recorrido con referencia
foreach ($flota as &$vehiculo) {
    // Formateo multibyte *
    $vehiculo['categoria_titulo'] = mb_convert_case($vehiculo['categoria'], MB_CASE_TITLE, 'UTF-8');
    $vehiculo['modelo_mayusculas'] = mb_strtoupper($vehiculo['modelo'], 'UTF-8');
    $vehiculo['longitud_modelo'] = mb_strlen($vehiculo['modelo'], 'UTF-8');

    // Diferencia entre existencia de clave y valor no nulo. *
    $vehiculo['tiene_clave_descuento'] = array_key_exists('descuento', $vehiculo);
    $vehiculo['descuento_no_nulo'] = isset($vehiculo['descuento']) && $vehiculo['descuento'] !== null;

    $vehiculo['tiene_clave_extras'] = array_key_exists('extras', $vehiculo);
    $vehiculo['extras_no_nulo'] = isset($vehiculo['extras']) && $vehiculo['extras'] !== null;
}
// Cierre de la referencia
unset($vehiculo);

// Orden descendente por autonomía y, en empate, por unidades disponibles.
usort($flota, static function (array $a, array $b): int {
    return ($b['autonomia_km'] <=> $a['autonomia_km'])
        ?: ($b['unidades_disponibles'] <=> $a['unidades_disponibles']);
});

// Preparacion de datos para JavaScript
$datosJS = array_map(static function (array $v): array {
    return [
        'modelo' => $v['modelo_mayusculas'],
        'categoria' => $v['categoria_titulo'],
        'autonomia_km' => $v['autonomia_km'],
        'unidades_disponibles' => $v['unidades_disponibles'],
        'descuento' => $v['descuento'],
        'extras' => $v['extras'] ?? [],
        'longitud_modelo' => $v['longitud_modelo'],
    ];
}, $flota);

// Codificacion segura a JSON
$flotaJS = json_encode(
    $datosJS,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
);

// Captura en bufer
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte EcoDrive</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #e8f5e9; }
    </style>
</head>
<body>
<h1>Reporte de flota EcoDrive</h1>

<table>
    <thead>
        <tr>
            <th>Modelo</th>
            <th>Categoría</th>
            <th>Autonomía km</th>
            <th>Unidades</th>
            <th>Longitud modelo</th>
            <th>¿Clave descuento?</th>
            <th>Descuento</th>
            <th>¿Clave extras?</th>
            <th>Extras</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($flota as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['modelo_mayusculas'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($v['categoria_titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
            <td><?= (int)$v['autonomia_km'] ?></td>
            <td><?= (int)$v['unidades_disponibles'] ?></td>
            <td><?= (int)$v['longitud_modelo'] ?></td>
            <td><?= $v['tiene_clave_descuento'] ? 'Sí' : 'No' ?></td>
            <td>
                <?= $v['descuento_no_nulo']
                    ? htmlspecialchars((string)$v['descuento'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    : '—' ?>
            </td>
            <td><?= $v['tiene_clave_extras'] ? 'Sí' : 'No' ?></td>
            <td>
                <?= $v['extras_no_nulo']
                    ? htmlspecialchars(implode(', ', $v['extras'] ?? []), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    : '—' ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
const flotaEcoDrive = <?= $flotaJS ?>;
console.log('Datos de flota seguros:', flotaEcoDrive);
</script>
</body>
</html>
<?php
$reporteHTML = ob_get_clean();

// Envío final del reporte capturado en memoria.
echo $reporteHTML;