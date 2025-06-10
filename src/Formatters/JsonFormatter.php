<?php

namespace Differ\Formatters\JsonFormatter;

function formatJson(array $diff): string
{
    $structured = convertToStructuredJson($diff);
    $json = json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        throw new \RuntimeException('Failed to encode JSON');
    }

    return $json;
}

/**
 * @param array<mixed> $diff
 * @return array<mixed>
 */
function convertToStructuredJson(array $diff): array
{
    return sortAssocArray(array_reduce(
        $diff,
        fn(array $acc, array $node): array => [
            ...$acc,
            $node['key'] => processNodeForJson($node)
        ],
        []
    ));
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
            array_map(prepareValueJson(...), (array)$value)
        ),
        is_array($value) => array_map(prepareValueJson(...), $value),
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

    return array_reduce(
        $sortedKeys,
        fn(array $acc, $key): array => [...$acc, $key => $array[$key]],
        []
    );
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
    return _merge($left, $right, $comparator, 0, 0, []);
}

/**
 * @template T
 * @param array<T> $left
 * @param array<T> $right
 * @param callable(T, T): int $comparator
 * @param int $leftIndex
 * @param int $rightIndex
 * @param array<T> $result
 * @return array<T>
 */
function _merge(array $left, array $right, callable $comparator, int $leftIndex, int $rightIndex, array $result): array
{
    if ($leftIndex >= count($left)) {
        return [...$result, ...array_slice($right, $rightIndex)];
    }

    if ($rightIndex >= count($right)) {
        return [...$result, ...array_slice($left, $leftIndex)];
    }

    return $comparator($left[$leftIndex], $right[$rightIndex]) <= 0
        ? _merge(
            $left,
            $right,
            $comparator,
            $leftIndex + 1,
            $rightIndex,
            [...$result, $left[$leftIndex]]
        )
        : _merge(
            $left,
            $right,
            $comparator,
            $leftIndex,
            $rightIndex + 1,
            [...$result, $right[$rightIndex]]
        );
}
