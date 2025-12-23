<div align="center">بسم الله الرحمن الرحيم</div>
<div align="left">

# :package_name

[![Latest Version on Packagist](https://img.shields.io/packagist/v/:vendor_slug/:package_slug.svg?style=for-the-badge&color=gray)](https://packagist.org/packages/:vendor_slug/:package_slug)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/:vendor_slug/:package_slug/pest.yml?branch=main&label=tests&style=for-the-badge&color=forestgreen)](https://github.com/:vendor_slug/:package_slug/actions?query=workflow%3Apest+branch%3Amain)
[![Coverage Status](https://img.shields.io/codecov/c/github/:vendor_slug/:package_slug/main?style=for-the-badge&color=purple)](https://codecov.io/gh/:vendor_slug/:package_slug)
[![Total Downloads](https://img.shields.io/packagist/dt/:vendor_slug/:package_slug.svg?style=for-the-badge&color=blue)](https://packagist.org/packages/:vendor_slug/:package_slug)

<img src="./.github/images/banner.png">

:package_description


## Installation

Install the package with [`Composer`](https://getcomposer.org/):

```bash
composer require :vendor_slug/:package_slug
```

Publish the assets they are copied to `public/vendor/:package_slug`.

```bash
php artisan vendor:publish --tag=":package_slug-assets"
```

You can publish and run the migrations using:

```bash
php artisan vendor:publish --tag=":package_slug-migrations"
php artisan migrate
```

You may also publish additional resources to tailor the package to your project:

- Config file to adjust defaults package settings:
  ```bash
  php artisan vendor:publish --tag=":package_slug-config"
  ```

  - Check the current configurations in [here](config/:package_slug.php).

- Views (if you need to override the Blade views):
  ```bash
  php artisan vendor:publish --tag=":package_slug-views"
  ```


## Usage

```php
$instance = new \VendorName\Skeleton();
echo $instance->somethingNonStatic();
// OR
\VendorName\Skeleton\Facades\Skeleton::somethingStatic();
```


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


## Support

Support ongoing package maintenance as well as the development of **other projects** through [sponsorship](https://github.com/sponsors/GoodM4ven) or one-time [donations](https://github.com/sponsors/GoodM4ven?frequency=one-time&sponsor=GoodM4ven) if you prefer.

### Credits
- [Blade Formatter](https://github.com/shufo/blade-formatter)
- [Larastan](https://github.com/larastan/larastan)
- [Prettier](https://github.com/prettier/prettier)
- [Pest](https://github.com/pestphp/pest-plugin-laravel)
- [Playwrite](https://playwrite.dev)
- [Orchestra](https://packages.tools)
- [Composer](https://getcomposer.org)
- [NPM](https://npmjs.com)
- [ESLint](https://eslint.org)
- [AnimeJS](https://animejs.com)
- [PHP](https://php.net)
- [TALL Stack Community](https://tallstack.dev)
- [TailwindCSS](https://tailwindcss.com)
- [AlpineJS](https://alpinejs.dev)
- [Livewire](https://livewire.laravel.com)
- [Laravel](https://laravel.com)
- [Spatie Team](https://github.com/Spatie)
- [VSC Codium](https://vscodium.com)
- [ChatGPT & Codex](https://developers.openai.com/codex)
- [Packagist](https://packagist.org)
- [Codecov](https://codecov.com)
- [Github Actions](https://github.com/actions)
- [GoodM4ven](https://github.com/GoodM4ven)
- [All Contributors](../../contributors)

</div>
<br>
<div align="center">والحمد لله رب العالمين</div>
