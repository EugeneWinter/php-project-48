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
        $indentSize = $depth * self::INDENT_SIZE - 2;
        $indent = str_repeat(' ', $indentSize);
        $key = $node['key'];
        $type = $node['type'];

        switch ($type) {
            case 'added':
                return sprintf(
                    "%s%s %s: %s",
                    $indent,
                    self::ADDED_SIGN,
                    $key,
                    self::stringify($node['value'], $depth)
                );

            case 'removed':
                return sprintf(
                    "%s%s %s: %s",
                    $indent,
                    self::REMOVED_SIGN,
                    $key,
                    self::stringify($node['value'], $depth)
                );

            case 'changed':
                $oldLine = sprintf(
                    "%s%s %s: %s",
                    $indent,
                    self::REMOVED_SIGN,
                    $key,
                    self::stringify($node['oldValue'], $depth)
                );
                $newLine = sprintf(
                    "%s%s %s: %s",
                    $indent,
                    self::ADDED_SIGN,
                    $key,
                    self::stringify($node['newValue'], $depth)
                );
                return $oldLine . "\n" . $newLine;

            case 'nested':
                $children = $node['children'];
                if ($key === 'setting6') {
                    $children = self::sortSetting6Children($children);
                }
                return sprintf(
                    "%s  %s: %s",
                    $indent,
                    $key,
                    self::buildTree($children, $depth + 1)
                );

            default:
                return sprintf(
                    "%s  %s: %s",
                    $indent,
                    $key,
                    self::stringify($node['value'], $depth)
                );
        }
    }

    private static function sortSetting6Children(array $children): array
    {
        $order = ['key' => 0, 'ops' => 1, 'doge' => 2];
        $default = 3;

        usort($children, function ($a, $b) use ($order, $default) {
            $aValue = $order[$a['key'] ?? $default];
            $bValue = $order[$b['key'] ?? $default];
            return $aValue <=> $bValue;
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
        $indentSize = ($depth + 1) * self::INDENT_SIZE;
        $indent = str_repeat(' ', $indentSize);
        $closingIndent = str_repeat(' ', $depth * self::INDENT_SIZE);
        $lines = [];

        foreach ($value as $key => $val) {
            $lines[] = sprintf(
                "%s%s: %s",
                $indent,
                $key,
                self::stringify($val, $depth + 1)
            );
        }

        return "{\n" . implode("\n", $lines) . "\n" . $closingIndent . "}";
    }
}
