<?php

$autos = [
    __DIR__ . '/vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

foreach ($autos as $file) {
    if (file_exists($file)) {
        require_once($file);
        break;
    }
}
