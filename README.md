# EcoDrive - Backend de Flota y Alquiler de Vehículos

Práctica 2: backend en PHP para gestionar reservas de vehículos eléctricos, con validación estricta, excepciones, multibyte y salida segura HTML/JS.

## Tecnologías
- PHP 8.0+
- Extensiones: mbstring, json
- Git y GitHub

## Estructura
- procesador.php: validación HTTP, facturación y excepciones
- reporte.php: flota, multibyte, ordenación y búfer
- README.md

## Ejecución local
php -S localhost:8000

Rutas de prueba:
- http://localhost:8000/procesador.php?dias=5
- http://localhost:8000/procesador.php?unidades=3
- http://localhost:8000/reporte.php

## Funcionalidad
- procesador.php: valida GET (dias o unidades), devuelve HTTP 400 si no es entero positivo, función calcularFactura() documentada con PHPDoc, excepciones y categorización por importe.
- reporte.php: formatea textos con mb_*, comprueba claves nulas, ordena con usort, captura con ob_start() y escapa HTML/JS.

## Seguridad
- htmlspecialchars con ENT_QUOTES | ENT_SUBSTITUTE.
- json_encode con JSON_HEX_TAG, JSON_HEX_APOS, JSON_HEX_AMP y JSON_HEX_QUOT.

Autor: Jeremy Gonzalez - Desarrollo Web en Entorno Servidor