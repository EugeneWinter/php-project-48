<?php

declare(strict_types=1);

namespace Differ\Formatters\StylishFormatter;

use Exception;

function formatStylish(array $diff): string
{
    $iter = function ($diff, $depth) use (&$iter) {
        $indent = str_repeat('    ', $depth);
        $lines = [];
        
        foreach (sortByKey($diff) as $node) {
            switch ($node['type']) {
                case 'nested':
                    $children = $iter($node['children'], $depth + 1);
                    $lines[] = "{$indent}    {$node['key']}: {\n{$children}\n{$indent}    }";
                    break;
                case 'changed':
                    $lines[] = "{$indent}  - {$node['key']}: " . toString($node['oldValue'], $depth);
                    $lines[] = "{$indent}  + {$node['key']}: " . toString($node['newValue'], $depth);
                    break;
                case 'added':
                    $lines[] = "{$indent}  + {$node['key']}: " . toString($node['value'], $depth);
                    break;
                case 'removed':
                    $lines[] = "{$indent}  - {$node['key']}: " . toString($node['value'], $depth);
                    break;
                case 'unchanged':
                    $lines[] = "{$indent}    {$node['key']}: " . toString($node['value'], $depth);
                    break;
            }
        }
        
        return implode("\n", $lines);
    };

    return "{\n" . $iter($diff, 0) . "\n}";
}

function toString(mixed $value, int $depth = 1): string
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
    $bracketIndent = str_repeat('    ', max($depth - 1, 0));
    $assoc = (array)$value;
    ksort($assoc);

    $lines = array_map(
        function ($key) use ($assoc, $depth, $indent) {
            $formattedValue = is_object($assoc[$key]) 
                ? toString($assoc[$key], $depth + 1)
                : toString($assoc[$key], $depth);
            return "{$indent}{$key}: {$formattedValue}";
        },
        array_keys($assoc)
    );

    return "{\n" . implode("\n", $lines) . "\n{$bracketIndent}}";
}

function sortByKey(array $nodes): array
{
    usort($nodes, fn($a, $b) => strcmp($a['key'], $b['key']));
    return $nodes;
}
