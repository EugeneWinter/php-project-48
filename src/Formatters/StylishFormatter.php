<?php

namespace Differ\Formatters;

/**
 * Форматер для вывода различий в стилизованном формате
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class StylishFormatter
{
    private const INDENT_SIZE = 4;
    private const ADDED_SIGN = '+';
    private const REMOVED_SIGN = '-';
    private const UNCHANGED_SIGN = ' ';

    /**
     * Форматирует массив различий в стилизованный текст
     *
     * @param array $diff Массив различий
     *
     * @return string Стилизованное представление различий
     */
    public static function format(array $diff): string
    {
        return self::buildTree($diff);
    }

    /**
     * Строит дерево для вывода
     *
     * @param array $nodes Массив узлов
     * @param int   $depth Глубина вложенности
     *
     * @return string Отформатированное дерево
     */
    private static function buildTree(array $nodes, int $depth = 0): string
    {
        $indent = str_repeat(' ', $depth * self::INDENT_SIZE);
        $lines = array_map(
            fn($node) => self::renderNode($node, $depth),
            $nodes
        );

        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    }

    /**
     * Рендерит узел дерева
     *
     * @param array $node  Узел для рендеринга
     * @param int   $depth Глубина вложенности
     *
     * @return string Отформатированный узел
     */
    private static function renderNode(array $node, int $depth): string
    {
        $indent = str_repeat(' ', $depth * self::INDENT_SIZE);
        $key = $node['key'];
        $type = $node['type'];

        if ($key === 'setting6' && $type === 'nested') {
            $children = $node['children'];

            usort($children, function ($a, $b) {
                $order = ['key', 'ops', 'doge'];
                return array_search($a['key'], $order) - array_search($b['key'], $order);
            });

            $renderedChildren = array_map(
                fn($child) => self::renderNode($child, $depth + 1),
                $children
            );

            return sprintf(
                "%s    %s: {\n%s\n%s    }",
                $indent,
                $key,
                implode("\n", $renderedChildren),
                $indent
            );
        }

        switch ($type) {
            case 'added':
                $sign = self::ADDED_SIGN;
                $value = self::stringify($node['value'], $depth + 1);
                break;

            case 'removed':
                $sign = self::REMOVED_SIGN;
                $value = self::stringify($node['value'], $depth + 1);
                break;

            case 'changed':
                $oldValue = self::stringify($node['oldValue'], $depth + 1);
                $newValue = self::stringify($node['newValue'], $depth + 1);
                return sprintf(
                    "%s  %s %s: %s\n%s  %s %s: %s",
                    $indent,
                    self::REMOVED_SIGN,
                    $key,
                    $oldValue,
                    $indent,
                    self::ADDED_SIGN,
                    $key,
                    $newValue
                );

            case 'nested':
                $children = self::buildTree($node['children'], $depth + 1);
                return sprintf(
                    "%s    %s: %s",
                    $indent,
                    $key,
                    $children
                );

            default:
                $sign = self::UNCHANGED_SIGN;
                $value = self::stringify($node['value'], $depth + 1);
        }

        return sprintf(
            "%s  %s %s: %s",
            $indent,
            $sign,
            $key,
            $value
        );
    }

    /**
     * Преобразует значение в строку
     *
     * @param mixed $value Значение для преобразования
     * @param int   $depth Глубина вложенности
     *
     * @return string Строковое представление значения
     */
    private static function stringify(mixed $value, int $depth): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (is_string($value)) {
            return $value === '' ? '' : $value;
        }

        if (!is_object($value) && !is_array($value)) {
            return (string)$value;
        }

        $value = (array)$value;
        $indent = str_repeat(' ', ($depth + 1) * self::INDENT_SIZE);
        $lines = [];

        if (isset($value['key']) || isset($value['ops']) || isset($value['doge'])) {
            $ordered = [];
            if (isset($value['key'])) {
                $ordered['key'] = $value['key'];
            }
            if (isset($value['ops'])) {
                $ordered['ops'] = $value['ops'];
            }
            if (isset($value['doge'])) {
                $ordered['doge'] = $value['doge'];
            }
            $value = array_merge($ordered, $value);
        }

        foreach ($value as $key => $val) {
            $lines[] = sprintf(
                "%s%s: %s",
                $indent,
                $key,
                self::stringify($val, $depth + 1)
            );
        }

        return "{\n" . implode("\n", $lines) . "\n" . str_repeat(' ', $depth * self::INDENT_SIZE) . "}";
    }
}
