<?php

namespace Differ\Formatters\Plain;

function format(array $diff): string
{
    $lines = buildLines($diff);
    return implode("\n", $lines);
}

function buildLines(array $diff, string $path = ''): array
{
    return array_reduce(
        $diff, function ($acc, $node) use ($path) {
            $currentPath = $path === '' ? $node['key'] : "{$path}.{$node['key']}";

            return match ($node['type']) {
                'added' => [...$acc, "Property '{$currentPath}' was added with value: " . stringifyValue($node['value'])],
                'removed' => [...$acc, "Property '{$currentPath}' was removed"],
                'changed' => [...$acc, "Property '{$currentPath}' was updated. From " . 
                stringifyValue($node['oldValue']) . " to " . stringifyValue($node['newValue'])],
                'nested' => [...$acc, ...buildLines($node['children'], $currentPath)],
                default => $acc
            };
        }, []
    );
}

function stringifyValue(mixed $value): string
{
    if (is_object($value) || is_array($value)) {
        return '[complex value]';
    }
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    if ($value === null) {
        return 'null';
    }
    return is_string($value) ? "'{$value}'" : (string)$value;
}
