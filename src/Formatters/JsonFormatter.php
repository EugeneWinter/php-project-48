<?php

/**
 * Форматер для преобразования различий в формат JSON
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */

namespace DiffGenerator\Formatters;

/**
 * Класс для форматирования различий в JSON
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class JsonFormatter
{
    /**
     * Форматирует массив различий в JSON строку
     *
     * @param array $diff Массив различий
     *
     * @return string JSON строка с различиями
     */
    public static function format(array $diff): string
    {
        $result = self::_convertToStructured($diff);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Преобразует плоский массив различий в структурированный массив
     *
     * @param array $diff Массив различий
     *
     * @return array Структурированный массив для преобразования в JSON
     */
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

    /**
     * Подготавливает значение для включения в JSON
     *
     * @param mixed $value Значение для подготовки
     *
     * @return mixed Подготовленное значение
     */
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
