<?php

$base = dirname(realpath(__DIR__)) . DIRECTORY_SEPARATOR;

return [
    'base' => $base,
    'shy' => $base . 'shy' . DIRECTORY_SEPARATOR,
    'app' => $base . 'app' . DIRECTORY_SEPARATOR,
    'cache' => $base . 'cache' . DIRECTORY_SEPARATOR,
    'public' => $base . 'public' . DIRECTORY_SEPARATOR
];