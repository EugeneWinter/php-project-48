<?php

namespace Differ\Differ;

use RuntimeException;
use stdClass;

use function Differ\Parsers\parse;
use function Differ\Formatters\format;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    $content1 = readFile($path1);
    $content2 = readFile($path2);

    $data1 = parse($content1, getFileFormat($path1));
    $data2 = parse($content2, getFileFormat($path2));

    $diff = buildDiff($data1, $data2);
    return format($diff, $format);
}

function readFile(string $path): string
{
    if (!file_exists($path)) {
        throw new RuntimeException("File not found: {$path}");
    }

    $content = file_get_contents($path);
    if ($content === false) {
        throw new RuntimeException("Failed to read file: {$path}");
    }

    return $content;
}

function getFileFormat(string $filePath): string
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($extension, ['json', 'yaml', 'yml'], true)) {
        throw new RuntimeException("Unsupported file extension: {$extension}");
    }
    return $extension === 'yml' ? 'yaml' : $extension;
}

function buildDiff(object $data1, object $data2): array
{
    $keys = array_unique(
        [
            ...array_keys((array)$data1),
            ...array_keys((array)$data2)
        ]
    );

    $filteredKeys = array_filter($keys, fn($key) => $key !== '');
    $sortedKeys = sortKeys($filteredKeys);

    return array_map(
        fn($key) => buildNode($key, $data1, $data2),
        $sortedKeys
    );
}

function buildNode(string $key, object $data1, object $data2): array
{
    $value1 = $data1->$key ?? null;
    $value2 = $data2->$key ?? null;

    if (!property_exists($data1, $key)) {
        return ['type' => 'added', 'key' => $key, 'value' => prepareValue($value2)];
    }

    if (!property_exists($data2, $key)) {
        return ['type' => 'removed', 'key' => $key, 'value' => prepareValue($value1)];
    }

    if (isObject($value1) && isObject($value2)) {
        return ['type' => 'nested', 'key' => $key, 'children' => buildDiff($value1, $value2)];
    }

    if ($value1 === $value2) {
        return ['type' => 'unchanged', 'key' => $key, 'value' => prepareValue($value1)];
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
    if (!is_object($value)) {
        return $value;
    }

    return (object) array_map(
        fn($prop) => prepareValue($prop),
        (array)$value
    );
}

function sortKeys(array $keys): array
{
    return array_reduce(
        $keys,
        function (array $carry, string $item): array {
            $inserted = false;
            $result = array_reduce(
                $carry,
                function (array $acc, string $current) use ($item, &$inserted): array {
                    if (!$inserted && strcasecmp($item, $current) < 0) {
                        $acc[] = $item;
                        $inserted = true;
                    }
                    $acc[] = $current;
                    return $acc;
                },
                []
            );

            if (!$inserted) {
                $result[] = $item;
            }

            return $result;
        },
        []
    );
}
