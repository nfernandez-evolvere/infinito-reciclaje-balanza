<?php

/**
 * Reduce el docblock que ide-helper escribe en cada modelo a solo las líneas
 * @mixin. Las anotaciones completas (@property/@method) quedan en
 * _ide_helper_models.php (clases IdeHelper*), referenciado vía @mixin.
 *
 * Se ejecuta automáticamente después de `ide-helper:models` mediante el
 * script de composer `ide-helper`. Ver composer.json.
 */

$models = glob(__DIR__ . '/../app/Models/*.php');
$changed = [];

foreach ($models as $file) {
    $code = file_get_contents($file);

    // Reemplaza el primer docblock que contenga un @mixin IdeHelper* por la
    // versión mínima. No toca ningún otro docblock ni el cuerpo de la clase.
    $new = preg_replace_callback('#/\*\*.*?\*/#s', function ($m) {
        if (preg_match('/@mixin\s+IdeHelper(\w+)/', $m[0], $mm)) {
            return "/**\n * @mixin \\Eloquent\n * @mixin IdeHelper{$mm[1]}\n */";
        }
        return $m[0];
    }, $code, 1);

    if ($new !== null && $new !== $code) {
        file_put_contents($file, $new);
        $changed[] = basename($file);
    }
}

echo 'Modelos limpiados: ' . count($changed) . PHP_EOL;
