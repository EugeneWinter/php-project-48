<?php

namespace DiffGenerator\Formatters;

class JsonFormatter
{
    public static function format(array $diff): string
    {
        $result = self::_convertToStructured($diff);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private static function _convertToStructured(array $diff): array
    {
        $result = [];

        foreach ($diff as $node) {
            $key = $node['key'];
            $type = $node['type'];

            switch ($type) {
                case 'added':
                    $result[$key] = [
                        'type' => 'added',
                        'value' => self::_prepareValue($node['value'])
                    ];
                    break;

                case 'removed':
                    $result[$key] = [
                        'type' => 'removed',
                        'value' => self::_prepareValue($node['value'])
                    ];
                    break;

                case 'changed':
                    $result[$key] = [
                        'type' => 'changed',
                        'oldValue' => self::_prepareValue($node['oldValue']),
                        'newValue' => self::_prepareValue($node['newValue'])
                    ];
                    break;

                case 'nested':
                    $result[$key] = self::_convertToStructured($node['children']);
                    break;

                default:
                    $result[$key] = self::_prepareValue($node['value']);
            }
        }

        return $result;
    }
    private static function _prepareValue($value)
    {
        if (is_object($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = self::_prepareValue($v);
            }
            return $result;
        }
        return $value;
    }
}
