<?php

$base = dirname(dirname(realpath(__DIR__))) . DIRECTORY_SEPARATOR;

return [
    'base' => $base,
    'app' => $base . 'app' . DIRECTORY_SEPARATOR,
    'view' => $base . 'app' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
    'cache' => $base . 'cache' . DIRECTORY_SEPARATOR,
    'public' => $base . 'public' . DIRECTORY_SEPARATOR,
    'shy' => $base . 'shy' . DIRECTORY_SEPARATOR
];
