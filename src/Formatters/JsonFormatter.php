<?php

namespace DiffGenerator\Formatters;

class JsonFormatter
{
    /**
     * Форматирует diff в JSON-строку
     */
    public static function format(array $diff): string
    {
        $result = self::convertToStructured($diff);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private static function convertToStructured(array $diff): array
    {
        $result = [];

        foreach ($diff as $node) {
            $key = $node['key'];
            $type = $node['type'];

            switch ($type) {
                case 'added':
                    $result[$key] = [
                        'type' => 'added',
                        'value' => self::prepareValue($node['value'])
                    ];
                    break;

                case 'removed':
                    $result[$key] = [
                        'type' => 'removed',
                        'value' => self::prepareValue($node['value'])
                    ];
                    break;

                case 'changed':
                    $result[$key] = [
                        'type' => 'changed',
                        'oldValue' => self::prepareValue($node['oldValue']),
                        'newValue' => self::prepareValue($node['newValue'])
                    ];
                    break;

                case 'nested':
                    $result[$key] = self::convertToStructured($node['children']);
                    break;

                default:
                    $result[$key] = self::prepareValue($node['value']);
            }
        }

        return $result;
    }

    private static function prepareValue($value)
    {
        if (is_object($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = self::prepareValue($v);
            }
            return $result;
        }
        return $value;
    }
}