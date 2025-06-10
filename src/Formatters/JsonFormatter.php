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
    $sortedKeys = array_sort_by($keys, fn($key) => (string)$key);

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
 * @param callable(T): mixed $callback
 * @return array<T>
 */
function array_sort_by(array $array, callable $callback): array
{
    $sorted = $array;
    usort($sorted, fn($a, $b) => strcmp($callback($a), $callback($b)));
    return $sorted;
}
