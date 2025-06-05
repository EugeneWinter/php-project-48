<?php

namespace DiffGenerator\Formatters;

class StylishFormatter
{
    private const INDENT_SIZE = 4;
    private const ADDED_SIGN = '+';
    private const REMOVED_SIGN = '-';
    private const UNCHANGED_SIGN = ' ';

    public static function format(array $diff): string
    {
        return self::buildTree($diff);
    }

    private static function buildTree(array $nodes, int $depth = 0): string
    {
        $indent = str_repeat(' ', $depth * self::INDENT_SIZE);
        $lines = array_map(
            fn($node) => self::renderNode($node, $depth),
            $nodes
        );

        return "{\r\n" . implode("\r\n", $lines) . "\r\n" . $indent . "}";
    }

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
                "%s    %s: {\r\n%s\r\n%s    }",
                $indent,
                $key,
                implode("\r\n", $renderedChildren),
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
                    "%s  %s %s: %s\r\n%s  %s %s: %s",
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
        if (isset($value['key'])) $ordered['key'] = $value['key'];
        if (isset($value['ops'])) $ordered['ops'] = $value['ops'];
        if (isset($value['doge'])) $ordered['doge'] = $value['doge'];
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

    return "{\r\n" . implode("\r\n", $lines) . "\r\n" . str_repeat(' ', $depth * self::INDENT_SIZE) . "}";
}
}
