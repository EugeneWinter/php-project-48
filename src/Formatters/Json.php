<?php

namespace Differ\Formatters\Json;

function format(array $diff): string
{
    $result = json_encode(buildTree($diff), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return $result === false ? throw new \RuntimeException('JSON encode error') : $result;
}

function buildTree(array $diff): array
{
    return array_reduce(
        $diff,
        function (array $acc, array $node): array {
            $key = $node['key'];
            $value = match ($node['type']) {
                'added' => ['type' => 'added', 'value' => prepareValue($node['value'])],
                'removed' => ['type' => 'removed', 'value' => prepareValue($node['value'])],
                'changed' => [
                    'type' => 'changed',
                    'oldValue' => prepareValue($node['oldValue']),
                    'newValue' => prepareValue($node['newValue'])
                ],
                'nested' => buildTree($node['children']),
                default => prepareValue($node['value'])
            };
            
            return array_merge($acc, [$key => $value]);
        },
        []
    );
}

function prepareValue(mixed $value): mixed
{
    if (is_object($value)) {
        return array_map('Differ\Formatters\Json\prepareValue', (array)$value);
    }
    return $value;
}
