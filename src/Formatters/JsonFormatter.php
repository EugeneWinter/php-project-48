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

namespace Differ\Formatters;

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
        $result = self::convertToStructured($diff);
        return json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Преобразует плоский массив различий в структурированный массив
     *
     * @param array $diff Массив различий
     *
     * @return array Структурированный массив для преобразования в JSON
     */
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

    /**
     * Подготавливает значение для включения в JSON
     *
     * @param mixed $value Значение для подготовки
     *
     * @return mixed Подготовленное значение
     */
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
