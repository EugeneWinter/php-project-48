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

        if (!is_object($value)) {
            return (string)$value;
        }

        $indent = str_repeat(' ', ($depth + 1) * self::INDENT_SIZE);
        $lines = [];
        $data = (array)$value;

        foreach ($data as $key => $val) {
            $lines[] = sprintf(
                "%s%s: %s",
                $indent,
                $key,
                self::stringify($val, $depth + 1)
            );
        }

        return "{\n" . implode("\n", $lines) . "\n" . $indent . "}";
    }
}