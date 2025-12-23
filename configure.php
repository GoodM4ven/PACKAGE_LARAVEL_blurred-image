#!/usr/bin/env php
<?php

function ask(string $question, string $default = ''): string
{
    $answer = readline($question.($default ? " ({$default})" : null).': ');

    if (! $answer) {
        return $default;
    }

    return $answer;
}

function confirm(string $question, bool $default = false): bool
{
    $answer = ask($question.' ('.($default ? 'Y/n' : 'y/N').')');

    if (! $answer) {
        return $default;
    }

    return strtolower($answer) === 'y';
}

function writeln(string $line): void
{
    echo $line.PHP_EOL;
}

function run(string $command): string
{
    $output = [];
    $exitCode = 0;

    exec($command . ' 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        throw new RuntimeException(
            "Command failed ({$exitCode}): {$command}\n" . implode("\n", $output)
        );
    }

    return trim(implode("\n", $output));
}

// function str_after(string $subject, string $search): string
// {
//     $pos = strrpos($subject, $search);

//     if ($pos === false) {
//         return $subject;
//     }

//     return substr($subject, $pos + strlen($search));
// }

function slugify(string $subject): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $subject), '-'));
}

function title_case(string $subject): string
{
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject)));
}

function title_snake(string $subject, string $replace = '_'): string
{
    return str_replace(['-', '_'], $replace, $subject);
}

function replace_in_file(string $file, array $replacements): void
{
    $contents = file_get_contents($file);

    // Protect strings that must NOT be replaced
    $protected = [
        'package:purge-skeleton',
    ];

    foreach ($protected as $i => $value) {
        $contents = str_replace(
            $value,
            "__PROTECTED_{$i}__",
            $contents
        );
    }

    // Perform normal replacements
    $contents = str_replace(
        array_keys($replacements),
        array_values($replacements),
        $contents
    );

    // Restore protected strings
    foreach ($protected as $i => $value) {
        $contents = str_replace(
            "__PROTECTED_{$i}__",
            $value,
            $contents
        );
    }

    file_put_contents($file, $contents);
}

function remove_prefix(string $prefix, string $content): string
{
    if (str_starts_with($content, $prefix)) {
        return substr($content, strlen($prefix));
    }

    return $content;
}

// function remove_composer_deps(array $names)
// {
//     $data = json_decode(file_get_contents(__DIR__.'/composer.json'), true);

//     foreach ($data['require-dev'] as $name => $version) {
//         if (in_array($name, $names, true)) {
//             unset($data['require-dev'][$name]);
//         }
//     }

//     file_put_contents(__DIR__.'/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
// }

// function remove_composer_script($scriptName)
// {
//     $data = json_decode(file_get_contents(__DIR__.'/composer.json'), true);

//     foreach ($data['scripts'] as $name => $script) {
//         if ($scriptName === $name) {
//             unset($data['scripts'][$name]);
//             break;
//         }
//     }

//     file_put_contents(__DIR__.'/composer.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
// }

function replace_readme_development_section(): void
{
    $file = 'README.md';
    $contents = file_get_contents($file);

    $original = <<<'MD'
## Development

- Just like Spatie's, you shuold run [configure.php](./configure.php) script to rename the template to your package. 

- Since the package is utilizing [Orchestra Testbench](https://packages.tools) for the testing environment, its configuration file [testbench.yaml](testbench.yaml) should be looked at.
- Running the Laravel Boost MCP server is done with `./vendor/bin/testbench boost:mcp` instead of `php artisan boost:mcp` or optionally via VSC command prompts.
  - Normally, VSC users should have their MCP client pointing at `./vendor/bin/testbench boost:mcp`. (Check [.vscode/mcp.json](.vscode/mcp.json))
  - HOWEVER, I have consistent configuration in [`laravel-boost-mcp.sh`](./laravel-boost-mcp.sh) file to look for both application setup's boost as well as the package's. And that's why we're pointing to that Bash file to handle the redirection.

### Codex MCP Connection

If you're using ChatGPT Codex in VSC or whatever, make sure your `config.toml` has at least the following:

```toml
[mcp_servers.laravel-boost]
command = "./laravel-boost-mcp.sh"
```

**I was only able to get it to work using the `@openai/cli` package ran via `npm`, and the VSC extension wan't able to establish a connection for some reason... Still, the VSC extension is very useful to ask when the environment is broken and retain full access to project files.**

### Workbench Laravel Environment

Keep in mind the following when using Workbench:
  - Run `./vendor/bin/testbench` instead of `artisan` for Laravel commands; maybe you'd create also an system terminal alias for it, I use `bench`.
    - Composer `scripts` listed in [composer.json](./composer.json) utilize it for the commands.
    - After running `composer serve`, visit `http://localhost:8000` to see the demo page in action.
  - Service providers are managed through [workbench/bootstrap/providers.php](./workbench/bootstrap/providers.php) array.
  - Environment variables are defined in [testbench.yaml](./testbench.yaml) file.

### Testing

```bash
composer test
```

> [!NOTE]
> For code coverage add `--coverage`, and for faster runs add `--parallel`.
MD;

    $replacement = <<<'MD'
## Development

This package was initiated based on my [Laravel package template](https://github.com/goodm4ven/TEMPLATE_PACKAGE_TALL/blob/main/README.md#development) that is built on top of [Spatie's](https://github.com/spatie/package-skeleton-laravel). Make sure to read the docs for both.
MD;

    $pattern = '/## Development\\n\\n.*?(?=\\n## [^\\n]+\\n|\\z)/s';

    $updated = preg_replace($pattern, $replacement."\n\n", $contents, 1, $count);

    if ($count === 0) {
        $updated = str_replace($original, $replacement, $contents, $count);
    }

    if ($count === 0) {
        return;
    }

    file_put_contents($file, $updated);
}

// function safeUnlink(string $filename)
// {
//     if (file_exists($filename) && is_file($filename)) {
//         unlink($filename);
//     }
// }

function determineSeparator(string $path): string
{
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
}

function replaceForWindows(): array
{
    return preg_split('/\\r\\n|\\r|\\n/', run('dir /S /B * | findstr /v /i .git\ | findstr /v /i .vendor\ | findstr /v /i '.basename(__FILE__).' | findstr /r /i /M /F:/ ":author :vendor :package VendorName skeleton migration_table_name vendor_name vendor_slug author@domain.com"'));
}

function replaceForAllOtherOSes(): array
{
    return explode(PHP_EOL, run('grep -E -r -l -i ":author|:vendor|:package|VendorName|skeleton|migration_table_name|vendor_name|vendor_slug|author@domain.com" --exclude-dir=vendor ./* ./.github/* | grep -v '.basename(__FILE__)));
}

function getGitHubApiEndpoint(string $endpoint): ?stdClass
{
    try {
        $curl = curl_init("https://api.github.com/{$endpoint}");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: spatie-configure-script/1.0',
            ],
        ]);

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($statusCode === 200) {
            return json_decode($response);
        }
    } catch (Exception $e) {
        // ignore
    }

    return null;
}

function searchCommitsForGitHubUsername(): string
{
    $authorName = strtolower(trim(shell_exec('git config user.name')));

    $committersRaw = shell_exec("git log --author='@users.noreply.github.com' --pretty='%an:%ae' --reverse");
    $committersLines = explode("\n", $committersRaw ?? '');
    $committers = array_filter(array_map(function ($line) use ($authorName) {
        $line = trim($line);
        [$name, $email] = explode(':', $line) + [null, null];

        return [
            'name' => $name,
            'email' => $email,
            'isMatch' => strtolower($name) === $authorName && ! str_contains($name, '[bot]'),
        ];
    }, $committersLines), fn ($item) => $item['isMatch']);

    if (empty($committers)) {
        return '';
    }

    $firstCommitter = reset($committers);

    return explode('@', $firstCommitter['email'])[0] ?? '';
}

function guessGitHubUsernameUsingCli()
{
    try {
        if (preg_match('/ogged in to github\.com as ([a-zA-Z-_]+).+/', shell_exec('gh auth status -h github.com 2>&1'), $matches)) {
            return $matches[1];
        }
    } catch (Exception $e) {
        // ignore
    }

    return '';
}

function guessGitHubUsername(): string
{
    $username = searchCommitsForGitHubUsername();
    if (! empty($username)) {
        return $username;
    }

    $username = guessGitHubUsernameUsingCli();
    if (! empty($username)) {
        return $username;
    }

    // fall back to using the username from the git remote
    $remoteUrl = shell_exec('git config remote.origin.url') ?? '';
    $remoteUrlParts = explode('/', str_replace(':', '/', trim($remoteUrl)));

    return $remoteUrlParts[1] ?? '';
}

function guessGitHubVendorInfo($authorName, $username): array
{
    $remoteUrl = shell_exec('git config remote.origin.url') ?? '';
    $remoteUrlParts = explode('/', str_replace(':', '/', trim($remoteUrl)));

    if (! isset($remoteUrlParts[1])) {
        return [$authorName, $username];
    }

    $response = getGitHubApiEndpoint("orgs/{$remoteUrlParts[1]}");

    if ($response === null) {
        return [$authorName, $username];
    }

    return [$response->name ?? $authorName, $response->login ?? $username];
}

function renameDirectory(string $from, string $to): void
{
    if (is_dir($from) && ! is_dir($to)) {
        rename($from, $to);
    }
}

// * ============
// * Questioning
// * ==========

$gitName = run('git config user.name');
$authorName = ask('Author name', $gitName);

$gitEmail = run('git config user.email');
$authorEmail = ask('Author email', $gitEmail);
$authorUsername = ask('Author username', guessGitHubUsername());

$guessGitHubVendorInfo = guessGitHubVendorInfo($authorName, $authorUsername);

$vendorName = ask('Vendor name', $guessGitHubVendorInfo[0]);
$vendorUsername = ask('Vendor username', $guessGitHubVendorInfo[1] ?? slugify($vendorName));
$vendorSlug = slugify($vendorUsername);

$vendorNamespace = str_replace('-', '', ucwords($vendorName));
$vendorNamespace = ask('Vendor namespace', $vendorNamespace);

$currentDirectory = getcwd();
$folderName = basename($currentDirectory);

$packageName = ask('Package name', $folderName);
$packageSlug = slugify($packageName);
$packageSlugWithoutPrefix = remove_prefix('laravel-', $packageSlug);

$className = title_case($packageName);
$className = ask('Class name', $className);
$variableName = lcfirst($className);
$description = ask('Package description', "This is my package {$packageSlug}");

writeln('------');
writeln("Author     : {$authorName} ({$authorUsername}, {$authorEmail})");
writeln("Vendor     : {$vendorName} ({$vendorSlug})");
writeln("Package    : {$packageSlug} <{$description}>");
writeln("Namespace  : {$vendorNamespace}\\{$className}");
writeln("Class name : {$className}");
writeln('------');
writeln('This script will replace the above values in all relevant files in the project directory.');
writeln('It will also initiate the backend packages setup via composer.');
writeln('------');

if (! confirm('Ready?', true)) {
    exit(1);
}

// * ===========
// * Processing
// * =========

// Rename package-name directories first
renameDirectory(
    __DIR__ . '/workbench/public/vendor/package-name',
    __DIR__ . '/workbench/public/vendor/' . $packageSlug,
);

$files = (str_starts_with(strtoupper(PHP_OS), 'WIN') ? replaceForWindows() : replaceForAllOtherOSes());
$additionalFiles = array_filter([
    __DIR__.'/config/package-name.php',
    __DIR__.'/resources/css/package-name.css',
    __DIR__.'/resources/js/package-name.js',
    __DIR__.'/tests/Browser/SkeletonBrowserTest.php',
    __DIR__.'/tests/SkeletonTest.php',
    __DIR__.'/workbench/public/vendor/'.$packageSlug.'/package-name.css',
    __DIR__.'/workbench/public/vendor/'.$packageSlug.'/package-name.js',
], 'file_exists');
$files = array_values(array_unique(array_merge($files, $additionalFiles)));

foreach ($files as $file) {
    replace_in_file($file, [
        ':author_name' => $authorName,
        ':author_username' => $authorUsername,
        'author@domain.com' => $authorEmail,
        ':vendor_name' => $vendorName,
        ':vendor_slug' => $vendorSlug,
        'VendorName' => $vendorNamespace,
        ':package_name' => $packageName,
        ':package_slug' => $packageSlug,
        ':package_slug_without_prefix' => $packageSlugWithoutPrefix,
        'Skeleton' => $className,
        'skeleton' => $packageSlug,
        'migration_table_name' => title_snake($packageSlug),
        'variable' => $variableName,
        ':package_description' => $description,
    ]);

    match (true) {
        str_contains($file, determineSeparator('src/Skeleton.php')) => rename($file, determineSeparator('./src/'.$className.'.php')),
        str_contains($file, determineSeparator('src/SkeletonServiceProvider.php')) => rename($file, determineSeparator('./src/'.$className.'ServiceProvider.php')),
        str_contains($file, determineSeparator('src/Facades/Skeleton.php')) => rename($file, determineSeparator('./src/Facades/'.$className.'.php')),
        str_contains($file, determineSeparator('src/Commands/SkeletonCommand.php')) => rename($file, determineSeparator('./src/Commands/'.$className.'Command.php')),
        str_contains($file, determineSeparator('src/Concerns/SkeletonConcern.php')) => rename($file, determineSeparator('./src/Concerns/'.$className.'Concern.php')),
        str_contains($file, determineSeparator('src/Concerns/Skeleton.php')) => rename($file, determineSeparator('./src/Concerns/'.$className.'.php')),
        str_contains($file, determineSeparator('database/migrations/create_skeleton_table.php.stub')) => rename($file, determineSeparator('./database/migrations/create_'.title_snake($packageSlugWithoutPrefix).'_table.php.stub')),
        str_contains($file, determineSeparator('resources/css/package-name.css')) => rename($file, determineSeparator('./resources/css/'.$packageSlug.'.css')),
        str_contains($file, determineSeparator('resources/js/package-name.js')) => rename($file, determineSeparator('./resources/js/'.$packageSlug.'.js')),
        str_contains($file, determineSeparator('tests/Browser/SkeletonBrowserTest.php')) => rename($file, determineSeparator('./tests/Browser/'.$className.'BrowserTest.php')),
        str_contains($file, determineSeparator('tests/SkeletonTest.php')) => rename($file, determineSeparator('./tests/'.$className.'Test.php')),
        str_contains($file, determineSeparator('config/package-name.php'))=> rename($file, determineSeparator('./config/'.$packageSlugWithoutPrefix.'.php')),
        str_contains($file, determineSeparator('resources/css/package-name.css'))=> rename($file, determineSeparator('./resources/css/'.$packageSlug.'.css')),
        str_contains($file, determineSeparator('resources/js/package-name.js'))=> rename($file, determineSeparator('./resources/js/'.$packageSlug.'.js')),
        str_contains($file, determineSeparator('workbench/public/vendor/'.$packageSlug.'/package-name.css')) => rename($file, determineSeparator('./workbench/public/vendor/'.$packageSlug.'/'.$packageSlug.'.css')),
        str_contains($file, determineSeparator('workbench/public/vendor/'.$packageSlug.'/package-name.js')) => rename($file, determineSeparator('./workbench/public/vendor/'.$packageSlug.'/'.$packageSlug.'.js')),
        default => [],
    };
}

replace_readme_development_section();

$directory = __DIR__;

run("ln -sf {$directory}/AGENTS.md {$directory}/workbench/AGENTS.md 2>&1");
run("ln -sf {$directory}/boost.json {$directory}/workbench/boost.json 2>&1");

// * ============
// * Conditioned
// * ==========

writeln('------');
writeln('Installing via composer now...');

try {
    run('composer install');
    run('./vendor/bin/testbench storage:link');
    run("ln -sf {$directory}/vendor {$directory}/workbench/vendor 2>&1");
} catch (\RuntimeException) {
    writeln('------');
    writeln('Installing via composer failed.');
    writeln('IMPORTANT: Make sure to make system-links for vendor and storage directories.');
}

writeln('------');
confirm('Do you want to run `npm install` to install frontend packages?') && (function() {
    writeln('------');
    writeln('Installing via npm now...');
    run('npm install');
})();

writeln('------');
confirm('What about running `composer green` to double check everything is okay now?') && (function() {
    writeln('------');
    writeln('Running the command now...');
    run('composer green');
})();

writeln('------');
confirm('Let this script delete itself?', true) && unlink(__FILE__);
