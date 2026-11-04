<?php

namespace Fayne\AdvancedText\Support;

final class AdvancedTextValue
{
    public static function editable(mixed $value, array $options): string
    {
        $core = (string) ($value ?? '');
        $prefix = (string) ($options['prefix'] ?? '');
        $suffix = (string) ($options['suffix'] ?? '');
        if (($options['prefixConcatenate'] ?? true) && $prefix !== '' && str_starts_with($core, $prefix)) {
            $core = substr($core, strlen($prefix));
        }
        if (($options['suffixConcatenate'] ?? true) && $suffix !== '' && str_ends_with($core, $suffix)) {
            $core = substr($core, 0, -strlen($suffix));
        }

        return $core;
    }

    public static function normalize(mixed $value, array $options): ?string
    {
        if ($value === null) {
            return null;
        }
        $core = self::editable($value, $options);

        return $core === '' ? '' : (($options['prefixConcatenate'] ?? true) ? ($options['prefix'] ?? '') : '') . $core . (($options['suffixConcatenate'] ?? true) ? ($options['suffix'] ?? '') : '');
    }
}
