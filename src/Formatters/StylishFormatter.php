<?php

namespace Differ\Formatters\StylishFormatter;

function formatStylish(array $diff, int $depth = 0): string
{
    $lines = array_map(function ($node) use ($depth) {
        $indent = str_repeat('    ', $depth);
        $key = $node['key'];
        
        switch ($node['type']) {
            case 'nested':
                $children = formatStylish($node['children'], $depth + 1);
                return "{$indent}    {$key}: {\n{$children}\n{$indent}    }";
            
            case 'added':
                $value = stringify($node['value'], $depth + 1);
                return "{$indent}  + {$key}: {$value}";
            
            case 'removed':
                $value = stringify($node['value'], $depth + 1);
                return "{$indent}  - {$key}: {$value}";
            
            case 'changed':
                $oldValue = stringify($node['oldValue'], $depth + 1);
                $newValue = stringify($node['newValue'], $depth + 1);
                return "{$indent}  - {$key}: {$oldValue}\n{$indent}  + {$key}: {$newValue}";
            
            case 'unchanged':
                $value = stringify($node['value'], $depth + 1);
                return "{$indent}    {$key}: {$value}";
        }
    }, $diff);

    return implode("\n", $lines);
}

function stringify(mixed $value, int $depth): string
{
    if (is_object($value)) {
        $props = (array)$value;
        $indent = str_repeat('    ', $depth);
        $lines = array_map(
            fn($key) => "{$indent}    {$key}: " . stringify($props[$key], $depth + 1),
            array_keys($props)
        );
        return "{\n" . implode("\n", $lines) . "\n{$indent}}";
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    return (string)$value;
}
