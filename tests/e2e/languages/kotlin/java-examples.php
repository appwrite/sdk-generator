<?php

declare(strict_types=1);

const UNSUPPORTED = [
    'general/create-excluded-general-fixture.md',
    'general/create-player.md',
    'general/enum.md',
    'zzexcludedservice/create-excluded-fixture.md',
];

[, $source, $destination] = $argv;
$parameters = $argv[3] ?? '';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
$paths = [];
foreach ($files as $file) {
    if ($file->getExtension() === 'md' && !in_array(substr((string) $file->getPathname(), strlen($source) + 1), UNSUPPORTED, true)) {
        $paths[] = $file->getPathname();
    }
}
sort($paths);

if ($paths === []) {
    fwrite(STDERR, "No Java examples found in {$source}\n");
    exit(1);
}

if (!is_dir($destination) && !mkdir($destination, 0777, true)) {
    exit(1);
}

foreach ($paths as $index => $path) {
    if (!preg_match('/```java\n(.*?)```/s', (string) file_get_contents($path), $match)) {
        fwrite(STDERR, "No Java block in {$path}\n");
        exit(1);
    }

    $imports = [];
    $statements = [];
    foreach (explode("\n", $match[1]) as $line) {
        if (str_starts_with($line, 'import ')) {
            $imports[] = $line;
        } else {
            $statements[] = '        ' . $line;
        }
    }

    $class = 'Example' . $index;
    file_put_contents(
        "{$destination}/{$class}.java",
        "package examples;\n\n"
        . implode("\n", $imports)
        . "\n\npublic class {$class} {\n    public void run({$parameters}) {\n"
        . implode("\n", $statements)
        . "\n    }\n}\n"
    );
}
