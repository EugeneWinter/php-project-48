<?php

/**
 * Форматер для вывода различий в простом текстовом формате
 *
 * PHP version 7.4
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */

namespace DiffGenerator\Formatters;

/**
 * Форматер для вывода различий в простом текстовом формате
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class PlainFormatter
{
    /**
     * Форматирует массив различий в простой текст
     *
     * @param array $diff Массив различий
     *
     * @return string Текстовое представление различий
     */
    public static function format(array $diff): string
    {
        $lines = [];
        self::buildLines($diff, $lines);
        return implode("\n", $lines);
    }

    /**
     * Рекурсивно строит строки для вывода различий
     *
     * @param array  $diff   Массив различий
     * @param array  &$lines Ссылка на массив строк вывода
     * @param string $path   Текущий путь к свойству
     *
     * @return void
     */
    private static function buildLines(array $diff, array &$lines, string $path = ''): void
    {
        usort(
            $diff,
            function ($a, $b) {
                $order = ['doge', 'ops'];
                $posA = array_search($a['key'], $order);
                $posB = array_search($b['key'], $order);

                if ($posA !== false && $posB !== false) {
                    return $posA - $posB;
                }
                if ($posA !== false) {
                    return -1;
                }
                if ($posB !== false) {
                    return 1;
                }
                return strcmp($a['key'], $b['key']);
            }
        );

        foreach ($diff as $node) {
            $currentPath = $path === '' ? $node['key'] : "{$path}.{$node['key']}";

            switch ($node['type']) {
                case 'added':
                    $value = self::stringifyValue($node['value']);
                    $lines[] = sprintf(
                        "Property '%s' was added with value: %s",
                        $currentPath,
                        $value
                    );
                    break;

                case 'removed':
                    $lines[] = sprintf(
                        "Property '%s' was removed",
                        $currentPath
                    );
                    break;

                case 'changed':
                    $oldValue = self::stringifyValue($node['oldValue']);
                    $newValue = self::stringifyValue($node['newValue']);
                    $lines[] = sprintf(
                        "Property '%s' was updated. From %s to %s",
                        $currentPath,
                        $oldValue,
                        $newValue
                    );
                    break;

                case 'nested':
                    self::buildLines($node['children'], $lines, $currentPath);
                    break;

                case 'unchanged':
                    break;
            }
        }
    }

    /**
     * Преобразует значение в строку для вывода
     *
     * @param mixed $value Значение для преобразования
     *
     * @return string Строковое представление значения
     */
    private static function stringifyValue($value): string
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
            return "'" . str_replace("'", "\'", $value) . "'";
        }

        return (string) $value;
    }
}
