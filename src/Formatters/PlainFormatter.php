<?php

namespace Differ\Formatters\PlainFormatter;

function formatPlain(array $diff): string
{
    $sortedDiff = sortDiffRecursive($diff);
    $lines = buildLines($sortedDiff);
    sort($lines);
    return implode("\n", $lines);
}

function sortDiffRecursive(array $diff): array
{
    usort($diff, fn($a, $b) => strcmp($a['key'], $b['key']));

    return array_map(
        function ($node) {
            if ($node['type'] === 'nested') {
                return [
                ...$node,
                'children' => sortDiffRecursive($node['children']),
                ];
            }
            return $node;
        },
        $diff
    );
}

function buildLines(array $diff, string $path = ''): array
{
    return array_reduce(
        $diff,
        function (array $acc, array $node) use ($path): array {
            $currentPath = $path === '' ? $node['key'] : "{$path}.{$node['key']}";

            return match ($node['type']) {
                'added' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was added with value: %s",
                        $currentPath,
                        stringifyValue($node['value'])
                    ),
                ],
                'removed' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was removed",
                        $currentPath
                    ),
                ],
                'changed' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was updated. From %s to %s",
                        $currentPath,
                        stringifyValue($node['oldValue']),
                        stringifyValue($node['newValue'])
                    ),
                ],
                'nested' => [
                    ...$acc,
                    ...buildLines($node['children'], $currentPath),
                ],
                'unchanged' => $acc,
                default => $acc,
            };
        },
        []
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

    if (is_string($value)) {
        return "'" . str_replace("'", "\\'", $value) . "'";
    }

    return (string) $value;
}
