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
        fn(array $acc, $key) => [...$acc, $key => $array[$key]],
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
    return array_reduce(
        $left,
        function (array $acc, $leftItem) use ($right, $comparator) {
            $rightItems = array_filter(
                $right,
                fn($rightItem) => $comparator($rightItem, $leftItem) < 0
            );

            $remainingRight = array_diff_key($right, array_flip(array_keys($rightItems)));

            return [
                'result' => [...$acc['result'], ...$rightItems, $leftItem],
                'remainingRight' => $remainingRight
            ];
        },
        ['result' => [], 'remainingRight' => $right]
    )['result'];
}
