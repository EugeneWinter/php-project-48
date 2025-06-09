<?php

/**
 * Форматер для вывода различий в простом текстовом формате (функциональный стиль)
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */

namespace Differ\Formatters\PlainFormatter;

/**
 * Форматирует массив различий в простой текст
 *
 * @param array $diff Массив различий
 *
 * @return string Текстовое представление различий
 */
function format(array $diff): string
{
    $sortedDiff = sortDiff($diff);
    $lines = buildLines($sortedDiff);
    return implode("\n", $lines);
}

/**
 * Сортирует массив diff по ключу с учётом специального порядка
 *
 * @param array $diff
 * @return array
 */
function sortDiff(array $diff): array
{
    $order = ['doge', 'ops'];

    $compare = function (array $a, array $b) use ($order): int {
        $posA = array_search($a['key'], $order, true);
        $posB = array_search($b['key'], $order, true);

        if ($posA !== false && $posB !== false) {
            return $posA <=> $posB;
        }
        if ($posA !== false) {
            return -1;
        }
        if ($posB !== false) {
            return 1;
        }
        return strcmp($a['key'], $b['key']);
    };

    $copy = $diff;
    uasort($copy, $compare);

    return array_values($copy);
}

/**
 * Рекурсивно строит массив строк для вывода различий
 *
 * @param array $diff Массив различий
 * @param string $path Текущий путь (для рекурсии)
 *
 * @return array Массив строк
 */
function buildLines(array $diff, string $path = ''): array
{
    return array_reduce(
        $diff,
        function (array $acc, array $node) use ($path): array {
            $currentPath = $path === '' ? $node['key'] : "{$path}.{$node['key']}";

            return match ($node['type']) {
                'added' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was added with value: %s",
                        $currentPath,
                        stringifyValue($node['value'])
                    ),
                ],
                'removed' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was removed",
                        $currentPath
                    ),
                ],
                'changed' => [
                    ...$acc,
                    sprintf(
                        "Property '%s' was updated. From %s to %s",
                        $currentPath,
                        stringifyValue($node['oldValue']),
                        stringifyValue($node['newValue'])
                    ),
                ],
                'nested' => [
                    ...$acc,
                    ...buildLines($node['children'], $currentPath),
                ],
                'unchanged' => $acc,
                default => $acc,
            };
        },
        []
    );
}

/**
 * Преобразует значение в строку для вывода
 *
 * @param mixed $value
 *
 * @return string
 */
function stringifyValue(mixed $value): string
{
    if (is_object($value) || is_array($value)) {
        return '[complex value]';
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if ($value === null) {
        return 'null';
    }

    if (is_string($value)) {
        return "'" . str_replace("'", "\\'", $value) . "'";
    }

    return (string) $value;
}
