<?php

namespace DiffGenerator\Formatters;

class PlainFormatter
{
    public static function format(array $diff): string
    {
        $lines = [];
        self::buildLines($diff, $lines);
        return implode("\n", $lines);
    }

    private static function buildLines(array $diff, array &$lines, string $path = ''): void
    {
        foreach ($diff as $node) {
            $currentPath = $path === '' ? $node['key'] : "{$path}.{$node['key']}";

            switch ($node['type']) {
                case 'added':
                    $value = self::stringifyValue($node['value']);
                    $lines[] = "Property '{$currentPath}' was added with value: {$value}";
                    break;

                case 'removed':
                    $lines[] = "Property '{$currentPath}' was removed";
                    break;

                case 'changed':
                    $oldValue = self::stringifyValue($node['oldValue']);
                    $newValue = self::stringifyValue($node['newValue']);
                    $lines[] = "Property '{$currentPath}' was updated. From {$oldValue} to {$newValue}";
                    break;

                case 'nested':
                    self::buildLines($node['children'], $lines, $currentPath);
                    break;

                case 'unchanged':
                    break;
            }
        }
    }

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