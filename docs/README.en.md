<p align="center"><img src="https://res.cloudinary.com/fayne/image/upload/v1787643269/fayne_logo.png" width="300"></p>

# Laravel Filament Advanced Text

[简体中文](../README.md) | English

For **PHP 8.4+, Laravel 13 and Filament 5**.

## Installation

Configure authorized Composer sources for this private package and its required `fayne/*` dependencies in the host first.

```bash
composer require fayne/laravel-filament-advanced-text
```

Laravel discovers the service provider automatically. Field-only packages need no Panel plugin. See [composer.json](../composer.json) for exact dependencies; do not copy private credentials from a development host.

## Features, usage and boundaries

Reusable text input for Filament 5 forms. The package depends on Laravel through Filament and does not require CMS Core, Page Builder, Page, or Menu.

```php
use Fayne\AdvancedText\Forms\Components\AdvancedText;

AdvancedText::make('sku')
    ->prefix('SKU-')
    ->suffix('-STD')
    ->prefixConcatenate()
    ->suffixConcatenate()
    ->options(['Basic' => 'basic', 'Pro' => 'pro'])
    ->datalist(['suggested-value'])
    ->remoteOptionsUrl(route('product-suggestions'))
    ->copyable()
    ->maxLength(40);
```

The editable core is limited by `maxLength`; configured affixes are excluded from that count. The saved value includes affixes enabled by `prefixConcatenate()` and `suffixConcatenate()`. Existing saved affixes are removed from the editable state before rendering, so repeated edits do not duplicate them. The default for both concatenate options is `true`.

Suggestions accept free input. `options()` maps visible labels to stored scalar values. Remote endpoints receive a debounced `query` parameter and return a JSON object of labels to scalar values. Same-origin requests use the current browser session. External URLs require exact URL entries in `config/filament-advanced-text.php` and valid CORS; external requests omit session credentials. Keep private credentials on a server-side endpoint.

Publish package configuration when external suggestions are needed:

```shell
php artisan vendor:publish --tag=filament-advanced-text-config
```

The package registers its view, translations, and configuration through Laravel package discovery. The custom input handles Unicode length counting, IME composition, stale remote responses, and clipboard failures. `readOnly()` only controls the field interaction; the host application remains responsible for authorization and preserving protected values.

## Testing and deployment

Run `composer install`, `composer test`, and `composer format` from the package directory. Automated checks do not replace host UI and target-environment acceptance.

## Cooperation and contributions

Open an issue with reproduction steps, versions and expected behavior. Include relevant checks and outstanding environment acceptance in pull requests. Never submit credentials or sensitive business data. For commercial cooperation and licensing, contact [Fayne Guo](mailto:fayne.guo@gmail.com).

## License

[Proprietary license](../LICENSE.md). Copying, distribution and use require authorization. Third-party dependencies retain their own licenses.

## Development and security

[Development and CI](../CONTRIBUTING.md) · [Security reports](../SECURITY.md). Run `composer check` for syntax, formatting and available standalone tests. Do not commit `composer.lock`.
