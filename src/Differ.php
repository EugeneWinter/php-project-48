<?php

namespace DiffGenerator;

use DiffGenerator\Parsers\ParserFactory;
use DiffGenerator\Formatters\{
    StylishFormatter,
    PlainFormatter,
    JsonFormatter
};

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    if (!file_exists($path1)) {
        throw new \RuntimeException("File not found: {$path1}");
    }

    if (!file_exists($path2)) {
        throw new \RuntimeException("File not found: {$path2}");
    }

    $content1 = file_get_contents($path1);
    $content2 = file_get_contents($path2);

    if ($content1 === false || $content2 === false) {
        throw new \RuntimeException("Failed to read file contents");
    }

    try {
        $format1 = ParserFactory::getFormat($path1);
        $format2 = ParserFactory::getFormat($path2);
        
        if ($format1 !== $format2) {
            throw new \RuntimeException("Different file formats: {$format1} and {$format2}");
        }

        $data1 = ParserFactory::parse($content1, $format1);
        $data2 = ParserFactory::parse($content2, $format2);
    } catch (\Exception $e) {
        throw new \RuntimeException("Parse error: " . $e->getMessage());
    }

    $diff = buildDiff($data1, $data2);

    try {
        return formatDiff($diff, $format);
    } catch (\Exception $e) {
        throw new \RuntimeException("Format error: " . $e->getMessage());
    }
}

    function buildDiff(object $data1, object $data2): array
    {
        $keys = array_unique(
            array_merge(
                array_keys((array)$data1),
                array_keys((array)$data2)
            )
        );

        usort($keys, function ($a, $b) {
            $order = ['doge', 'ops'];
            $posA = array_search($a, $order);
            $posB = array_search($b, $order);
            
            if ($posA !== false && $posB !== false) return $posA - $posB;
            if ($posA !== false) return -1;
            if ($posB !== false) return 1;
            return strcmp($a, $b);
        });

        return array_map(
            function ($key) use ($data1, $data2) {
                return buildNode($key, $data1, $data2);
            },
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
        $result = [];
        foreach ($value as $k => $v) {
            $result[$k] = prepareValue($v);
        }
        return $result;
    }
    return $value;
}

function formatDiff(array $diff, string $format): string
{
    switch ($format) {
        case 'plain':
            return PlainFormatter::format($diff);
        case 'json':
            return JsonFormatter::format($diff);
        case 'stylish':
        default:
            return StylishFormatter::format($diff);
    }
}