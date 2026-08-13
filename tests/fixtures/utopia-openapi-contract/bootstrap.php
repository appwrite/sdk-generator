<?php

declare(strict_types=1);

\spl_autoload_register(static function (string $class): void {
    if (!\str_starts_with($class, 'Utopia\\OpenAPI\\')) {
        return;
    }

    $file = __DIR__ . '/' . \str_replace('\\', '/', $class) . '.php';
    if (\is_file($file)) {
        require $file;
    }
});
