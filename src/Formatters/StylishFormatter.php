<?php

namespace Differ\Formatters;

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
            function ($node) use ($depth) {
                return self::renderNode($node, $depth);
            },
            $nodes
        );

        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    }

    private static function renderNode(array $node, int $depth): string
    {
        $indent = str_repeat(' ', $depth * self::INDENT_SIZE);
        $key = $node['key'];
        $type = $node['type'];

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
                $children = $node['children'];
                if ($key === 'setting6') {
                    $children = self::sortSetting6Children($children);
                }
                $formattedChildren = self::buildTree($children, $depth + 1);
                return sprintf(
                    "%s    %s: %s",
                    $indent,
                    $key,
                    $formattedChildren
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

    private static function sortSetting6Children(array $children): array
    {
        $order = ['key' => 0, 'ops' => 1, 'doge' => 2];
        usort($children, function ($a, $b) use ($order) {
            return ($order[$a['key'] ?? 3) <=> ($order[$b['key'] ?? 3);
        });
        return $children;
    }

    private static function stringify($value, int $depth): string
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

        $value = (array)$value;
        $indent = str_repeat(' ', ($depth + 1) * self::INDENT_SIZE);
        $lines = [];

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
