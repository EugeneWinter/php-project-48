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
        return sortAssocArray(array_map('prepareValueJson', $assoc));
    }

    if (is_array($value)) {
        return array_map('prepareValueJson', $value);
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
    if (empty($left)) {
        return $right;
    }

    if (empty($right)) {
        return $left;
    }

    if ($comparator($left[0], $right[0]) <= 0) {
        return [array_shift($left), ...merge($left, $right, $comparator)];
    }

    return [array_shift($right), ...merge($left, $right, $comparator)];
}
