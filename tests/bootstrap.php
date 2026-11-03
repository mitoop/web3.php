<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__).'/.env';

if (! is_file($envFile)) {
    return;
}

foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);

    if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
        continue;
    }

    [$name, $value] = explode('=', $line, 2);
    $name = trim($name);
    $value = trim($value);

    if ($name === '' || getenv($name) !== false) {
        continue;
    }

    putenv("{$name}={$value}");
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
}
