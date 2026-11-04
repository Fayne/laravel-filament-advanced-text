<?php

use Fayne\AdvancedText\Forms\Components\AdvancedText;
use Fayne\AdvancedText\Support\AdvancedTextValue;
use Filament\Forms\Components\TextInput;

it('normalizes affixes once and preserves null, empty and zero values', function () {
    $options = ['prefix' => 'SKU-', 'suffix' => '-STD'];

    expect(AdvancedTextValue::editable('SKU-123-STD', $options))->toBe('123')
        ->and(AdvancedTextValue::normalize('123', $options))->toBe('SKU-123-STD')
        ->and(AdvancedTextValue::normalize('', $options))->toBe('')
        ->and(AdvancedTextValue::normalize(null, $options))->toBeNull()
        ->and(AdvancedTextValue::normalize('0', $options))->toBe('SKU-0-STD');
});

it('is a reusable Filament TextInput with a standalone namespace', function () {
    expect(is_subclass_of(AdvancedText::class, TextInput::class))->toBeTrue()
        ->and(method_exists(AdvancedText::class, 'remoteOptionsUrl'))->toBeTrue()
        ->and(method_exists(AdvancedText::class, 'prefixConcatenate'))->toBeTrue()
        ->and(method_exists(AdvancedText::class, 'suffixConcatenate'))->toBeTrue();
});
