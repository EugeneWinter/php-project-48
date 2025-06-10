<?php

namespace Differ\Differ;

use RuntimeException;
use Exception;
use stdClass;

use function Differ\Parsers\getFormat;
use function Differ\Parsers\parse;
use function Differ\Formatters\JsonFormatter\formatJson;
use function Differ\Formatters\PlainFormatter\formatPlain;
use function Differ\Formatters\StylishFormatter\formatStylish;

function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    validateFiles($path1, $path2);

    $content1 = file_get_contents($path1);
    $content2 = file_get_contents($path2);

    if ($content1 === false || $content2 === false) {
        throw new RuntimeException('Failed to read file contents');
    }

    try {
        $format1 = getFormat($path1);
        $format2 = getFormat($path2);

        if ($format1 !== $format2) {
            throw new RuntimeException(
                sprintf('Different file formats: %s and %s', $format1, $format2)
            );
        }

        $data1 = parse($content1, $format1);
        $data2 = parse($content2, $format2);
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
 * @return array<int, array<string, mixed>>
 */
function buildDiff(object $data1, object $data2): array
{
    $data1Array = (array)$data1;
    $data2Array = (array)$data2;

    $keys = array_unique(array_merge(
        array_keys($data1Array),
        array_keys($data2Array)
    ));

    $sortedKeys = sortKeys($keys);

    return array_map(
        fn(string $key): array => buildNode($key, $data1, $data2),
        $sortedKeys
    );
}

/**
 * @param string $key
 * @param object $data1
 * @param object $data2
 * @return array<string, mixed>
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

/**
 * @param mixed $value
 */
function isObject($value): bool
{
    return is_object($value) && !($value instanceof \DateTime);
}

/**
 * @param mixed $value
 * @return mixed
 */
function prepareValue($value)
{
    if (is_object($value)) {
        $props = (array)$value;
        $result = new stdClass();
        foreach (array_keys($props) as $k) {
            $result->$k = prepareValue($props[$k]);
        }
        return $result;
    }
    return $value;
}

/**
 * @param array<string> $keys
 * @return array<string>
 */
function sortKeys(array $keys): array
{
    $sortedKeys = $keys;
    natcasesort($sortedKeys);
    return array_values($sortedKeys);
}

/**
 * @param array<int, array<string, mixed>> $diff
 */
function formatDiff(array $diff, string $format): string
{
    return match ($format) {
        'stylish' => formatStylish($diff),
        'plain' => formatPlain($diff),
        'json' => formatJson($diff),
        default => throw new RuntimeException("Unknown format: {$format}"),
    };
}
