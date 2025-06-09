<?php

/**
 * Форматер для преобразования различий в формат JSON (функциональный стиль)
 *
 * @category DiffGenerator
 * @package  Formatters
 * @author   Eugene Winter
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */

namespace Differ\Formatters\JsonFormatter;

/**
 * Форматирует массив различий в JSON строку
 *
 * @param array $diff Массив различий
 *
 * @return string JSON строка с различиями
 */
function format(array $diff): string
{
    $structured = convertToStructured($diff);
    $json = json_encode($structured, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($json === false) {
        throw new \RuntimeException('Failed to encode JSON');
    }

    return $json;
}

/**
 * Преобразует плоский массив различий в структурированный массив
 *
 * @param array $diff Массив различий
 *
 * @return array Структурированный массив
 */
function convertToStructured(array $diff): array
{
    return array_reduce(
        $diff,
        function (array $acc, array $node): array {
            $key = $node['key'];
            $type = $node['type'];

            $value = match ($type) {
                'added' => [
                    'type' => 'added',
                    'value' => prepareValue($node['value']),
                ],
                'removed' => [
                    'type' => 'removed',
                    'value' => prepareValue($node['value']),
                ],
                'changed' => [
                    'type' => 'changed',
                    'oldValue' => prepareValue($node['oldValue']),
                    'newValue' => prepareValue($node['newValue']),
                ],
                'nested' => convertToStructured($node['children']),
                default => prepareValue($node['value']),
            };

            return [...$acc, $key => $value];
        },
        []
    );
}

/**
 * Подготавливает значение для включения в JSON
 *
 * @param mixed $value Значение
 *
 * @return mixed Подготовленное значение
 */
function prepareValue(mixed $value): mixed
{
    if (is_object($value)) {
        $assoc = (array) $value;

        return array_reduce(
            array_keys($assoc),
            function (array $acc, string $key) use ($assoc): array {
                return [...$acc, $key => prepareValue($assoc[$key])];
            },
            []
        );
    }

    return $value;
}
