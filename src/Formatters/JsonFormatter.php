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
    $result = array_reduce(
        $diff,
        function (array $acc, array $node): array {
            $key = $node['key'];
            $type = $node['type'];

            $value = match ($type) {
                'added' => [
                    'type' => 'added',
                    'value' => prepareValueJson($node['value']),
                ],
                'removed' => [
                    'type' => 'removed',
                    'value' => prepareValueJson($node['value']),
                ],
                'changed' => [
                    'type' => 'changed',
                    'oldValue' => prepareValueJson($node['oldValue']),
                    'newValue' => prepareValueJson($node['newValue']),
                ],
                'nested' => convertToStructuredJson($node['children']),
                default => prepareValueJson($node['value']),
            };

            return [...$acc, $key => $value];
        },
        []
    );

    return sortAssocArray($result);
}

/**
 * @param mixed $value
 * @return mixed
 */
function prepareValueJson(mixed $value)
{
    if (is_object($value)) {
        $assoc = (array) $value;
        return sortAssocArray(array_map(
            fn($v) => prepareValueJson($v),
            $assoc
        ));
    }

    if (is_array($value)) {
        return array_map(
            fn($v) => prepareValueJson($v),
            $value
        );
    }

    return $value;
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
        function (array $acc, $key) use ($array): array {
            return [...$acc, $key => $array[$key]];
        },
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
    $left = array_slice($array, 0, $mid);
    $right = array_slice($array, $mid);

    return merge(
        mergeSort($left, $comparator),
        mergeSort($right, $comparator),
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
    if ($leftIndex >= count($left) || $rightIndex >= count($right)) {
        return [...$result, ...array_slice($left, $leftIndex), ...array_slice($right, $rightIndex)];
    }

    if ($comparator($left[$leftIndex], $right[$rightIndex]) <= 0) {
        return _merge(
            $left,
            $right,
            $comparator,
            $leftIndex + 1,
            $rightIndex,
            [...$result, $left[$leftIndex]]
        );
    }

    return _merge(
        $left,
        $right,
        $comparator,
        $leftIndex,
        $rightIndex + 1,
        [...$result, $right[$rightIndex]]
    );
}
