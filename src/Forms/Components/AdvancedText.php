<?php

namespace Fayne\AdvancedText\Forms\Components;

use Closure;
use Fayne\AdvancedText\Support\AdvancedTextValue;
use Filament\Forms\Components\TextInput;

class AdvancedText extends TextInput
{
    protected string $view = 'filament-advanced-text::advanced-text';

    protected array $textOptions = [];

    protected ?array $allowedUrls = null;

    public function allowedRemoteUrls(array $urls): static
    {
        $this->allowedUrls = $urls;

        return $this;
    }

    public function suggestions(array $suggestions): static
    {
        return $this->datalist($suggestions);
    }

    public function remoteOptionsUrl(?string $url): static
    {
        $this->textOptions['remote_options_url'] = $url;

        return $this;
    }

    public function prefixConcatenate(bool $condition = true): static
    {
        $this->textOptions['prefixConcatenate'] = $condition;

        return $this;
    }

    public function suffixConcatenate(bool $condition = true): static
    {
        $this->textOptions['suffixConcatenate'] = $condition;

        return $this;
    }

    public function options(array $mapping): static
    {
        $this->textOptions['options'] = $mapping;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->afterStateHydrated(function (AdvancedText $component, mixed $state): void {
            if ($state === null && $component->hasDefaultState()) {
                $component->state($component->getDefaultState());
            }
        });
    }

    public function getAdvancedTextConfig(): array
    {
        return [
            'prefix' => (string) ($this->getPrefixLabel() ?? ''),
            'suffix' => (string) ($this->getSuffixLabel() ?? ''),
            'prefixConcatenate' => $this->textOptions['prefixConcatenate'] ?? true,
            'suffixConcatenate' => $this->textOptions['suffixConcatenate'] ?? true,
            'mapping' => $this->textOptions['options'] ?? [],
            'suggestions' => array_values($this->getDatalistOptions() ?? []),
            'remoteUrl' => $this->textOptions['remote_options_url'] ?? null,
            'allowedUrls' => $this->allowedUrls ?? config('filament-advanced-text.remote_urls', []),
            'copyable' => $this->isCopyable(),
            'readonly' => $this->isReadOnly(),
            'maxLength' => $this->getMaxLength(),
        ];
    }

    public function editableValue(mixed $value): string
    {
        return AdvancedTextValue::editable($value, $this->getAdvancedTextConfig());
    }

    public function normalizeValue(mixed $value): ?string
    {
        return AdvancedTextValue::normalize($value, $this->getAdvancedTextConfig());
    }

    public function mutatesDehydratedState(): bool
    {
        return true;
    }

    public function mutateDehydratedState(mixed $state): mixed
    {
        return $this->normalizeValue(parent::mutateDehydratedState($state));
    }

    public function mutateStateForValidation(mixed $state): mixed
    {
        return $this->normalizeValue(parent::mutateStateForValidation($state));
    }

    public function getLengthValidationRules(): array
    {
        $rules = parent::getLengthValidationRules();
        if (($max = $this->getMaxLength()) !== null) {
            $rules = array_values(array_filter($rules, fn ($rule) => $rule !== 'max:' . $max));
            $rules[] = function (string $attribute, mixed $value, Closure $fail) use ($max): void {
                if (mb_strlen($this->editableValue($value)) > $max) {
                    $fail(__('filament-advanced-text::messages.length', ['max' => $max]));
                }
            };
        }

        return $rules;
    }
}
