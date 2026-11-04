<?php

namespace Fayne\AdvancedText;

use Illuminate\Support\ServiceProvider;

class AdvancedTextServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-advanced-text.php', 'filament-advanced-text');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-advanced-text');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'filament-advanced-text');
        $this->publishes([__DIR__ . '/../config/filament-advanced-text.php' => config_path('filament-advanced-text.php')], 'filament-advanced-text-config');
    }
}
