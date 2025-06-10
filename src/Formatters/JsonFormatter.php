<?php

namespace Differ\Formatters\JsonFormatter;

function formatJson(array $diff): string
{
    $structured = convertToStructuredJson($diff);
    $json = json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    return $json ?: throw new \RuntimeException('Failed to encode JSON');
}

/**
 * @param array<mixed> $diff
 * @return array<mixed>
 */
function convertToStructuredJson(array $diff): array
{
    $result = array_reduce(
        $diff,
        function (array $acc, array $node): array {
            $key = $node['key'];
            $value = processNodeForJson($node);
            return [...$acc, $key => $value];
        },
        []
    );

    return sortAssocArray($result);
}

/**
 * @param array<string, mixed> $node
 * @return mixed
 */
function processNodeForJson(array $node)
{
    return match ($node['type']) {
        'added' => [
            'type' => 'added',
            'value' => prepareValueJson($node['value'])
        ],
        'removed' => [
            'type' => 'removed',
            'value' => prepareValueJson($node['value'])
        ],
        'changed' => [
            'type' => 'changed',
            'oldValue' => prepareValueJson($node['oldValue']),
            'newValue' => prepareValueJson($node['newValue'])
        ],
        'nested' => convertToStructuredJson($node['children']),
        default => prepareValueJson($node['value'])
    };
}

/**
 * @param mixed $value
 * @return mixed
 */
function prepareValueJson(mixed $value)
{
    return match (true) {
        is_object($value) => sortAssocArray(
            arrayMapRecursive(prepareValueJson(...), (array)$value)
        ),
        is_array($value) => arrayMapRecursive(prepareValueJson(...), $value),
        default => $value
    };
}

/**
 * @param array<mixed> $array
 * @return array<mixed>
 */
function sortAssocArray(array $array): array
{
    $keys = array_keys($array);
    $sortedKeys = mergeSort($keys, fn($a, $b) => strcmp((string)$a, (string)$b));

    return array_combine($sortedKeys, array_map(
        fn($key) => $array[$key],
        $sortedKeys
    ));
}

/**
 * @template T
 * @param array<T> $array
 * @param callable(T, T): int $comparator
 * @return array<T>
 */
function mergeSort(array $array, callable $comparator): array
{
    if (count($array) <= 1) {
        return $array;
    }

    $mid = (int)(count($array) / 2);
    return merge(
        mergeSort(array_slice($array, 0, $mid), $comparator),
        mergeSort(array_slice($array, $mid), $comparator),
        $comparator
    );
}

/**
 * @template T
 * @param array<T> $left
 * @param array<T> $right
 * @param callable(T, T): int $comparator
 * @return array<T>
 */
function merge(array $left, array $right, callable $comparator): array
{
    $result = [];
    $leftIndex = $rightIndex = 0;

    while ($leftIndex < count($left) && $rightIndex < count($right)) {
        $result[] = $comparator($left[$leftIndex], $right[$rightIndex]) <= 0
            ? $left[$leftIndex++]
            : $right[$rightIndex++];
    }

    return [...$result, ...array_slice($left, $leftIndex), ...array_slice($right, $rightIndex)];
}

/**
 * @template T
 * @param callable(mixed): T $callback
 * @param array<mixed> $array
 * @return array<T>
 */
function arrayMapRecursive(callable $callback, array $array): array
{
    return array_map(
        fn($item) => is_array($item) ? arrayMapRecursive($callback, $item) : $callback($item),
        $array
    );
}
