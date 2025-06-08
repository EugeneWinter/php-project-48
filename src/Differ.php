<?php

namespace Differ\Differ;

use Differ\Parsers\ParserFactory;
use Differ\Formatters\{
    StylishFormatter,
    PlainFormatter,
    JsonFormatter
};
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

function buildDiff(object $data1, object $data2): array
{
    $data1Array = (array)$data1;
    $data2Array = (array)$data2;
    
    $keys = array_keys($data1Array);
    
    foreach (array_keys($data2Array) as $key) {
        if (!in_array($key, $keys)) {
            $keys[] = $key;
        }
    }
    
    return array_map(
        fn($key) => buildNode($key, $data1, $data2),
        $keys
    );
}
function buildNode(string $key, object $data1, object $data2): array
{
    $value1 = $data1->$key ?? null;
    $value2 = $data2->$key ?? null;

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
        $result = new \stdClass();
        foreach ($value as $k => $v) {
            $result->{$k} = prepareValue($v);
        }
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

function formatOutput(array $diff, int $indent = 2): string
{
    $lines = ["{"];
    foreach ($diff as $node) {
        $lines[] = formatNode($node, $indent);
    }
    $lines[] = str_repeat(' ', $indent - 2) . "}";
    return implode("\n", $lines);
}

function formatNode(array $node, int $indent): string
{
    $spaces = str_repeat(' ', $indent);
    $key = $node['key'];
    
    switch ($node['type']) {
        case 'added':
            return "{$spaces}+ {$key}: " . formatValue($node['value'], $indent + 4);
        case 'removed':
            return "{$spaces}- {$key}: " . formatValue($node['value'], $indent + 4);
        case 'unchanged':
            return "{$spaces}  {$key}: " . formatValue($node['value'], $indent + 4);
        case 'changed':
            return "{$spaces}- {$key}: " . formatValue($node['oldValue'], $indent + 4) . 
                   "\n{$spaces}+ {$key}: " . formatValue($node['newValue'], $indent + 4);
        case 'nested':
            $children = formatOutput($node['children'], $indent + 4);
            return "{$spaces}  {$key}: " . ltrim($children);
        default:
            throw new \RuntimeException("Unknown node type: {$node['type']}");
    }
}

function formatValue(mixed $value, int $indent): string
{
    if (is_object($value)) {
        $lines = ["{"];
        foreach ($value as $k => $v) {
            $childSpaces = str_repeat(' ', $indent + 4);
            $lines[] = "{$childSpaces}{$k}: " . formatValue($v, $indent + 4);
        }
        $lines[] = str_repeat(' ', $indent) . "}";
        return implode("\n", $lines);
    }
    
    if (is_string($value)) {
        return $value;
    }
    
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }
    
    if (is_null($value)) {
        return 'null';
    }
    
    return (string)$value;
}
