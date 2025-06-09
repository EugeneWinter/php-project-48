<?php

namespace Differ;

use Differ\Parsers\ParserFactory;
use function Differ\Formatters\JsonFormatter\formatJson;
use function Differ\Formatters\PlainFormatter\formatPlain;
use function Differ\Formatters\StylishFormatter\formatStylish;
use RuntimeException;
use Exception;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    validateFiles($path1, $path2);

    $content1 = file_get_contents($path1);
    $content2 = file_get_contents($path2);

    if ($content1 === false || $content2 === false) {
        throw new RuntimeException('Failed to read file contents');
    }

    try {
        $format1 = ParserFactory::getFormat($path1);
        $format2 = ParserFactory::getFormat($path2);

        if ($format1 !== $format2) {
            throw new RuntimeException(
                sprintf('Different file formats: %s and %s', $format1, $format2)
            );
        }

        $data1 = ParserFactory::parse($content1, $format1);
        $data2 = ParserFactory::parse($content2, $format2);
    } catch (Exception $e) {
        throw new RuntimeException(
            sprintf('Parse error: %s', $e->getMessage())
        );
    }

    $diff = buildDiff($data1, $data2);

    try {
        return formatDiff($diff, $format);
    } catch (Exception $e) {
        throw new RuntimeException(
            sprintf('Format error: %s', $e->getMessage())
        );
    }
}

function validateFiles(string $path1, string $path2): void
{
    if (!file_exists($path1)) {
        throw new RuntimeException(sprintf('File not found: %s', $path1));
    }

    if (!file_exists($path2)) {
        throw new RuntimeException(sprintf('File not found: %s', $path2));
    }
}

/**
 * @param object $data1
 * @param object $data2
 * @return array
 */
function buildDiff(object $data1, object $data2): array
{
    $data1Array = (array)$data1;
    $data2Array = (array)$data2;

    $keys = array_unique(array_merge(array_keys($data1Array), array_keys($data2Array)));

    $sortedKeys = $keys;
    usort($sortedKeys, fn($a, $b) => $a <=> $b);

    return array_map(
        fn(string $key) => buildNode($key, $data1, $data2),
        $sortedKeys
    );
}

/**
 * @param string $key
 * @param object $data1
 * @param object $data2
 * @return array
 */
function buildNode(string $key, object $data1, object $data2): array
{
    $value1 = property_exists($data1, $key) ? $data1->$key : null;
    $value2 = property_exists($data2, $key) ? $data2->$key : null;

    if (!property_exists($data1, $key)) {
        return [
            'type' => 'added',
            'key' => $key,
            'value' => prepareValue($value2)
        ];
    }

    if (!property_exists($data2, $key)) {
        return [
            'type' => 'removed',
            'key' => $key,
            'value' => prepareValue($value1)
        ];
    }

    if (isObject($value1) && isObject($value2)) {
        return [
            'type' => 'nested',
            'key' => $key,
            'children' => buildDiff($value1, $value2)
        ];
    }

    if ($value1 === $value2) {
        return [
            'type' => 'unchanged',
            'key' => $key,
            'value' => prepareValue($value1)
        ];
    }

    return [
        'type' => 'changed',
        'key' => $key,
        'oldValue' => prepareValue($value1),
        'newValue' => prepareValue($value2)
    ];
}

function isObject(mixed $value): bool
{
    return is_object($value) && !($value instanceof \DateTime);
}

function prepareValue(mixed $value): mixed
{
    if (is_object($value)) {
        $props = (array)$value;
        $result = array_reduce(
            array_keys($props),
            function ($carry, $k) use ($props) {
                $carry->{$k} = prepareValue($props[$k]);
                return $carry;
            },
            new \stdClass()
        );
        return $result;
    }
    return $value;
}

function formatDiff(array $diff, string $format): string
{
    if ($format === 'stylish') {
        return formatOutput($diff);
    }
    return match ($format) {
        'plain' => PlainFormatter::format($diff),
        'json' => JsonFormatter::format($diff),
        default => throw new \RuntimeException("Unknown format: {$format}"),
    };
}

function formatOutput(array $tree, int $depth = 1): string
{
    $indentSize = 4;
    $currentIndent = str_repeat(' ', $depth * $indentSize - 2);
    $bracketIndent = str_repeat(' ', ($depth - 1) * $indentSize);

    $lines = array_merge(
        ['{'],
        array_map(
            fn(array $node) => formatNode($node, $depth),
            $tree
        ),
        ["{$bracketIndent}}"]
    );

    return implode("\n", $lines);
}

function formatNode(array $node, int $depth): string
{
    $indentSize = 4;
    $currentIndent = str_repeat(' ', $depth * $indentSize - 2);
    $key = $node['key'];

    return match ($node['type']) {
        'nested' => "{$currentIndent}  {$key}: " . formatOutput($node['children'], $depth + 1),
        'unchanged' => "{$currentIndent}  {$key}: " . formatValue($node['value'], $depth + 1),
        'added' => "{$currentIndent}+ {$key}: " . formatValue($node['value'], $depth + 1),
        'removed' => "{$currentIndent}- {$key}: " . formatValue($node['value'], $depth + 1),
        'changed' => "{$currentIndent}- {$key}: " . formatValue($node['oldValue'], $depth + 1) . "\n"
                   . "{$currentIndent}+ {$key}: " . formatValue($node['newValue'], $depth + 1),
        default => throw new \RuntimeException("Unknown node type: {$node['type']}"),
    };
}

function formatValue(mixed $value, int $depth): string
{
    if (!is_object($value)) {
        return formatPrimitive($value);
    }

    $indentSize = 4;
    $currentIndent = str_repeat(' ', $depth * $indentSize);
    $bracketIndent = str_repeat(' ', ($depth - 1) * $indentSize);

    $lines = array_merge(
        ['{'],
        array_map(
            fn($key) => "{$currentIndent}{$key}: " . formatValue($value->$key, $depth + 1),
            array_keys((array)$value)
        ),
        ["{$bracketIndent}}"]
    );

    return implode("\n", $lines);
}

function formatPrimitive(mixed $value): string
{
    return match (true) {
        is_bool($value) => $value ? 'true' : 'false',
        $value === null => 'null',
        default => (string) $value,
    };
}
