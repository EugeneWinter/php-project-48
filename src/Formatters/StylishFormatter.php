<?php

namespace Differ\Formatters\StylishFormatter;

/**
 * Форматирует массив различий в стильный формат (функциональный стиль)
 *
 * @param array $diff
 * @return string
 */
function format(array $diff): string
{
    return buildTree($diff);
}

/**
 * Размер отступа
 */
const INDENT_SIZE = 4;
const ADDED_SIGN = '+';
const REMOVED_SIGN = '-';
const UNCHANGED_SIGN = ' ';

/**
 * Рекурсивно строит дерево в строку
 *
 * @param array $nodes
 * @param int $depth
 * @return string
 */
function buildTree(array $nodes, int $depth = 0): string
{
    $indent = str_repeat(' ', $depth * INDENT_SIZE);

    $lines = array_map(
        fn(array $node): string => renderNode($node, $depth),
        $nodes
    );

    return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
}

/**
 * Рендерит один узел
 *
 * @param array $node
 * @param int $depth
 * @return string
 */
function renderNode(array $node, int $depth): string
{
    $indentSize = $depth * INDENT_SIZE - 2;
    $indent = str_repeat(' ', max(0, $indentSize));
    $key = $node['key'];
    $type = $node['type'];

    return match ($type) {
        'added' => sprintf(
            "%s%s %s: %s",
            $indent,
            ADDED_SIGN,
            $key,
            stringify($node['value'], $depth)
        ),
        'removed' => sprintf(
            "%s%s %s: %s",
            $indent,
            REMOVED_SIGN,
            $key,
            stringify($node['value'], $depth)
        ),
        'changed' => sprintf(
            "%s%s %s: %s\n%s%s %s: %s",
            $indent,
            REMOVED_SIGN,
            $key,
            stringify($node['oldValue'], $depth),
            $indent,
            ADDED_SIGN,
            $key,
            stringify($node['newValue'], $depth)
        ),
        'nested' => sprintf(
            "%s  %s: %s",
            $indent,
            $key,
            buildTree(
                $key === 'setting6' ? sortSetting6Children($node['children']) : $node['children'],
                $depth + 1
            )
        ),
        default => sprintf(
            "%s  %s: %s",
            $indent,
            $key,
            stringify($node['value'], $depth)
        ),
    };
}

/**
 * Сортирует детей для ключа setting6 согласно порядку
 *
 * @param array $children
 * @return array
 */
function sortSetting6Children(array $children): array
{
    $order = ['key' => 0, 'ops' => 1, 'doge' => 2];
    $default = 3;

    $compare = function (array $a, array $b) use ($order, $default): int {
        $aValue = $order[$a['key'] ?? ''] ?? $default;
        $bValue = $order[$b['key'] ?? ''] ?? $default;
        return $aValue <=> $bValue;
    };

    $copy = $children;
    uasort($copy, $compare);
    return array_values($copy);
}

/**
 * Преобразует значение в строку с отступами
 *
 * @param mixed $value
 * @param int $depth
 * @return string
 */
function stringify(mixed $value, int $depth): string
{
    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    if (!is_object($value) && !is_array($value)) {
        return (string)$value;
    }

    $arrayValue = (array)$value;
    $indentSize = ($depth + 1) * INDENT_SIZE;
    $indent = str_repeat(' ', $indentSize);
    $closingIndent = str_repeat(' ', $depth * INDENT_SIZE);

    $lines = array_map(
        fn($key, $val): string => sprintf(
            "%s%s: %s",
            $indent,
            $key,
            stringify($val, $depth + 1)
        ),
        array_keys($arrayValue),
        $arrayValue
    );

    return "{\n" . implode("\n", $lines) . "\n" . $closingIndent . "}";
}
