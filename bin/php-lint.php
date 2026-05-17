#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    'bin',
    'config',
    'database',
    'src',
    'tests',
];

$files = [];

foreach ($paths as $path) {
    $absolutePath = $root.DIRECTORY_SEPARATOR.$path;

    if (! is_dir($absolutePath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || ! $file->isFile()) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $files[] = $file->getPathname();
    }
}

sort($files);

$failed = [];

foreach ($files as $file) {
    $process = proc_open(
        [PHP_BINARY, '-l', $file],
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $root
    );

    if (! is_resource($process)) {
        fwrite(STDERR, "Unable to start PHP lint process for {$file}\n");
        exit(1);
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);

    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        $failed[] = [
            'file' => $file,
            'output' => trim((string) $stdout."\n".(string) $stderr),
        ];
    }
}

if ($failed !== []) {
    foreach ($failed as $failure) {
        fwrite(STDERR, $failure['file'].PHP_EOL);
        fwrite(STDERR, $failure['output'].PHP_EOL.PHP_EOL);
    }

    fwrite(STDERR, count($failed).' PHP file(s) failed syntax checks.'.PHP_EOL);
    exit(1);
}

echo 'PHP syntax OK ('.count($files).' files).'.PHP_EOL;
