<?php

declare(strict_types=1);

namespace Differ\Formatters\StylishFormatter;

use Exception;

function formatStylish(array $diff): string
{
    $iter = function ($diff, $depth) use (&$iter) {
        $indent = str_repeat('    ', $depth);
        $lines = [];
        
        foreach ($diff as $node) {
            switch ($node['type']) {
                case 'nested':
                    $children = $iter($node['children'], $depth + 1);
                    $lines[] = "{$indent}    {$node['key']}: {\n{$children}\n{$indent}    }";
                    break;
                case 'changed':
                    $lines[] = "{$indent}  - {$node['key']}: " . toString($node['oldValue'], $depth + 1);
                    $lines[] = "{$indent}  + {$node['key']}: " . toString($node['newValue'], $depth + 1);
                    break;
                case 'added':
                    $lines[] = "{$indent}  + {$node['key']}: " . toString($node['value'], $depth + 1);
                    break;
                case 'removed':
                    $lines[] = "{$indent}  - {$node['key']}: " . toString($node['value'], $depth + 1);
                    break;
                case 'unchanged':
                    $lines[] = "{$indent}    {$node['key']}: " . toString($node['value'], $depth + 1);
                    break;
            }
        }
        
        return implode("\n", $lines);
    };

    return "{\n" . $iter($diff, 0) . "\n}";
}

function toString(mixed $value, int $depth): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    if (!is_object($value)) {
        return (string)$value;
    }

    $indent = str_repeat('    ', $depth);
    $props = array_map(
        fn($key) => "{$indent}    {$key}: " . toString($value->$key, $depth + 1),
        array_keys((array)$value)
    );

    return "{\n" . implode("\n", $props) . "\n{$indent}}";
}