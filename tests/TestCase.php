<?php

namespace Fayne\AdvancedText\Tests;

use Fayne\AdvancedText\AdvancedTextServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [TranslationServiceProvider::class, AdvancedTextServiceProvider::class];
    }
}
