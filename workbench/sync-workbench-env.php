<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);
$testbenchPath = $rootPath.'/testbench.yaml';
$workbenchPath = $rootPath.'/workbench';

if (! file_exists($testbenchPath)) {
    fwrite(STDERR, "Missing testbench.yaml at {$testbenchPath}\n");

    exit(1);
}

if (! is_dir($workbenchPath)) {
    fwrite(STDERR, "Missing workbench directory at {$workbenchPath}\n");

    exit(1);
}

$variables = extractTestbenchEnv($testbenchPath);

if ($variables === []) {
    fwrite(STDERR, "No environment variables found in {$testbenchPath}\n");

    exit(1);
}

writeEnvFiles($variables, $workbenchPath);

fwrite(STDOUT, sprintf(
    // "Synced %d environment variables to %s/.env and %s/.env.example\n",
    "Synced %d environment variables to workbench directory\n",
    count($variables),
    // $workbenchPath,
    // $workbenchPath
));

/**
 * @return array<string, string>
 */
function extractTestbenchEnv(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        return [];
    }

    $variables = [];
    $inEnvBlock = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if (! $inEnvBlock) {
            if ($trimmed === 'env:') {
                $inEnvBlock = true;
            }

            continue;
        }

        $isIndented = str_starts_with($line, ' ') || str_starts_with($line, "\t");

        if (! $isIndented && $trimmed !== '') {
            break;
        }

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        if (! str_contains($line, ':')) {
            continue;
        }

        [$key, $value] = explode(':', $line, 2);

        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $value = trimQuotes($value);

        $variables[$key] = $value;
    }

    return $variables;
}

function writeEnvFiles(array $variables, string $workbenchPath): void
{
    $targets = [
        $workbenchPath.'/.env',
        $workbenchPath.'/.env.example',
    ];

    foreach ($targets as $target) {
        if (file_exists($target)) {
            unlink($target);
        }

        $lines = [];

        foreach ($variables as $key => $value) {
            $lines[] = "{$key}={$value}";
        }

        file_put_contents($target, implode(PHP_EOL, $lines).PHP_EOL);
    }
}

function trimQuotes(string $value): string
{
    $quotes = [
        '"' => '"',
        "'" => "'",
    ];

    foreach ($quotes as $start => $end) {
        if (str_starts_with($value, $start) && str_ends_with($value, $end)) {
            return substr($value, 1, -1);
        }
    }

    return $value;
}
