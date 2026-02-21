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
syncWorkbenchLinks($workbenchPath);
ensureWorkbenchGitignoreEntries($workbenchPath);

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

function syncWorkbenchLinks(string $workbenchPath): void
{
    $links = [
        '.ai' => '../.ai',
        '.agents' => '../.agents',
        'AGENTS.md' => '../AGENTS.md',
        'boost.json' => '../boost.json',
    ];

    foreach ($links as $linkName => $target) {
        $linkPath = $workbenchPath.'/'.$linkName;
        ensureSymlink($target, $linkPath);
    }
}

function ensureWorkbenchGitignoreEntries(string $workbenchPath): void
{
    $gitignorePath = $workbenchPath.'/.gitignore';
    $requiredEntries = [
        '/.agents',
        '/.ai',
        'AGENTS.md',
        'boost.json',
    ];

    $existingLines = file_exists($gitignorePath)
        ? (file($gitignorePath, FILE_IGNORE_NEW_LINES) ?: [])
        : [];

    foreach ($requiredEntries as $entry) {
        if (! in_array($entry, $existingLines, true)) {
            $existingLines[] = $entry;
        }
    }

    file_put_contents($gitignorePath, implode(PHP_EOL, $existingLines).PHP_EOL);
}

function ensureSymlink(string $target, string $linkPath): void
{
    if (is_link($linkPath) && readlink($linkPath) === $target) {
        return;
    }

    removePath($linkPath);

    $created = @symlink($target, $linkPath);

    if (! $created) {
        fwrite(STDERR, "Warning: failed to create symlink {$linkPath} -> {$target}\n");
    }
}

function removePath(string $path): void
{
    if (is_link($path) || is_file($path)) {
        @unlink($path);

        return;
    }

    if (! is_dir($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());

            continue;
        }

        @unlink($item->getPathname());
    }

    @rmdir($path);
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
