<?php

declare(strict_types=1);

namespace Differ\Formatters\StylishFormatter;

use Exception;

function formatStylish(array $diff): string
{
    $iter = function ($diff, $depth) use (&$iter) {
        $indent = str_repeat('    ', $depth);
        $lines = array_map(function ($node) use ($iter, $depth, $indent) {
            switch ($node['type']) {
                case 'added':
                    return "{$indent}  + {$node['key']}: " . toString($node['value'], $depth + 1);
                case 'removed':
                    return "{$indent}  - {$node['key']}: " . toString($node['value'], $depth + 1);
                case 'unchanged':
                    return "{$indent}    {$node['key']}: " . toString($node['value'], $depth + 1);
                case 'changed':
                    return [
                        "{$indent}  - {$node['key']}: " . toString($node['oldValue'], $depth + 1),
                        "{$indent}  + {$node['key']}: " . toString($node['newValue'], $depth + 1)
                    ];
                case 'nested':
                    $children = $iter($node['children'], $depth + 1);
                    return "{$indent}    {$node['key']}: {\n{$children}\n{$indent}    }";
                default:
                    throw new Exception("Unknown node type: {$node['type']}");
            }
        }, $diff);

        return implode("\n", array_merge(...array_map(
            fn($line) => is_array($line) ? $line : [$line],
            $lines
        )));
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