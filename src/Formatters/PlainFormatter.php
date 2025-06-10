<?php

namespace Differ\Formatters\PlainFormatter;

function formatPlain(array $diff): string
{
    $sortedDiff = sortDiffRecursive($diff);
    $lines = buildLines($sortedDiff);
    $sortedLines = sortArray($lines);
    return implode("\n", $sortedLines);
}

function sortDiffRecursive(array $diff): array
{
    $sorted = sortArray(
        $diff,
        fn(array $a, array $b): int => $a['key'] <=> $b['key']
    );

    return array_map(
        function (array $node): array {
            return $node['type'] === 'nested'
                ? [...$node, 'children' => sortDiffRecursive($node['children'])]
                : $node;
        },
        $sorted
    );
}

/**
 * @param array<mixed> $array
 * @param callable(mixed, mixed): int|null $callback
 * @return array<mixed>
 */
function sortArray(array $array, ?callable $callback = null): array
{
    $comparator = $callback ?? fn($a, $b) => $a <=> $b;
    return mergeSort($array, $comparator);
}

/**
 * @param array<mixed> $array
 * @param callable(mixed, mixed): int $comparator
 * @return array<mixed>
 */
function mergeSort(array $array, callable $comparator): array
{
    if (count($array) <= 1) {
        return $array;
    }

    $mid = (int)(count($array) / 2);
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);

    return merge(
        mergeSort($left, $comparator),
        mergeSort($right, $comparator),
        $comparator
    );
}

/**
 * @param array<mixed> $left
 * @param array<mixed> $right
 * @param callable(mixed, mixed): int $comparator
 * @return array<mixed>
 */
function merge(array $left, array $right, callable $comparator): array
{
    $result = [];
    $leftIndex = 0;
    $rightIndex = 0;

    while ($leftIndex < count($left) && $rightIndex < count($right)) {
        if ($comparator($left[$leftIndex], $right[$rightIndex]) <= 0) {
            $result[] = $left[$leftIndex];
            $leftIndex++;
        } else {
            $result[] = $right[$rightIndex];
            $rightIndex++;
        }
    }

    return [
        ...$result,
        ...array_slice($left, $leftIndex),
        ...array_slice($right, $rightIndex)
    ];
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
