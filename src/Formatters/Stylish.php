<?php

namespace Differ\Formatters\Stylish;

function format(array $diff, int $depth = 0): string
{
    $indent = str_repeat('    ', $depth);
    $lines = array_map(
        function ($node) use ($depth, $indent) {
            $key = $node['key'];
            $type = $node['type'];

            switch ($type) {
                case 'nested':
                    $children = format($node['children'], $depth + 1);
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
                default:
                    throw new \Exception("Unknown node type: {$type}");
            }
        },
        $diff
    );

    $result = implode("\n", $lines);
    return $depth === 0 ? "{\n{$result}\n}" : $result;
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

    if ($value === null) {
        return 'null';
    }

    return (string)$value;
}
