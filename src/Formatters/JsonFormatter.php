<?php

namespace Differ\Formatters\JsonFormatter;

/**
 * Форматирует массив различий в JSON строку
 */
function formatJson(array $diff): string
{
    $structured = convertToStructuredJson($diff);
    $json = json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        throw new \RuntimeException('Failed to encode JSON');
    }

    return $json;
}

function convertToStructuredJson(array $diff): array
{
    return array_reduce(
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
}

function prepareValueJson(mixed $value): mixed
{
    if (is_object($value)) {
        $assoc = (array) $value;

        return array_reduce(
            array_keys($assoc),
            function (array $acc, string $key) use ($assoc): array {
                return [...$acc, $key => prepareValueJson($assoc[$key])];
            },
            []
        );
    }

    return $value;
}
