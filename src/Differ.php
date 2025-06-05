<?php

/**
 * Генератор различий между файлами
 *
 * Сравнивает два файла (JSON/YAML) и возвращает различия в указанном формате
 *
 * @category DiffGenerator
 * @package  DiffGenerator
 * @author   EugeneWinter corvoattano200529@gmail.com
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */

namespace DiffGenerator;

use DiffGenerator\Parsers\ParserFactory;
use DiffGenerator\Formatters\{
    StylishFormatter,
    PlainFormatter,
    JsonFormatter
};

/**
 * Генерирует различия между двумя файлами
 *
 * @param string $path1 Путь к первому файлу
 * @param string $path2 Путь ко второму файлу
 * @param string $format Формат вывода (stylish, plain, json)
 *
 * @return string Различия в указанном формате
 *
 * @throws \RuntimeException Если файлы не существуют или имеют разные форматы
 */
function genDiff(string $path1, string $path2, string $format = 'stylish'): string
{
    if (!file_exists($path1)) {
        throw new \RuntimeException("File not found: {$path1}");
    }

    if (!file_exists($path2)) {
        throw new \RuntimeException("File not found: {$path2}");
    }

    $content1 = file_get_contents($path1);
    $content2 = file_get_contents($path2);

    if ($content1 === false || $content2 === false) {
        throw new \RuntimeException("Failed to read file contents");
    }

    try {
        $format1 = ParserFactory::getFormat($path1);
        $format2 = ParserFactory::getFormat($path2);
        
        if ($format1 !== $format2) {
            throw new \RuntimeException("Different file formats: {$format1} and {$format2}");
        }

        $data1 = ParserFactory::parse($content1, $format1);
        $data2 = ParserFactory::parse($content2, $format2);
    } catch (\Exception $e) {
        throw new \RuntimeException("Parse error: " . $e->getMessage());
    }

    $diff = buildDiff($data1, $data2);

    try {
        return formatDiff($diff, $format);
    } catch (\Exception $e) {
        throw new \RuntimeException("Format error: " . $e->getMessage());
    }
}

/**
 * Строит дерево различий между двумя объектами
 *
 * @param object $data1 Первый объект для сравнения
 * @param object $data2 Второй объект для сравнения
 *
 * @return array Массив с описанием различий
 */
function buildDiff(object $data1, object $data2): array
{
    $keys = array_unique(
        array_merge(
            array_keys((array)$data1),
            array_keys((array)$data2)
        )
    );
    sort($keys);

    return array_map(
        function ($key) use ($data1, $data2) {
            return buildNode($key, $data1, $data2);
        },
        $keys
    );
}

/**
 * Строит узел различий для конкретного ключа
 *
 * @param string $key Ключ для сравнения
 * @param object $data1 Первый объект
 * @param object $data2 Второй объект
 *
 * @return array Описание различия для ключа
 */
function buildNode(string $key, object $data1, object $data2): array
{
    $value1 = $data1->$key ?? null;
    $value2 = $data2->$key ?? null;

    if (!property_exists($data1, $key)) {
        return [
            'type' => 'added',
            'key' => $key,
            'value' => prepareValue($value2)
        ];
    }

    if (!property_exists($data2, $key)) {
        return [
            'type' => 'removed',
            'key' => $key,
            'value' => prepareValue($value1)
        ];
    }

    if (isObject($value1) && isObject($value2)) {
        return [
            'type' => 'nested',
            'key' => $key,
            'children' => buildDiff($value1, $value2)
        ];
    }

    if ($value1 === $value2) {
        return [
            'type' => 'unchanged',
            'key' => $key,
            'value' => prepareValue($value1)
        ];
    }

    return [
        'type' => 'changed',
        'key' => $key,
        'oldValue' => prepareValue($value1),
        'newValue' => prepareValue($value2)
    ];
}

/**
 * Проверяет, является ли значение объектом (исключая DateTime)
 *
 * @param mixed $value Проверяемое значение
 *
 * @return bool True если это объект (не DateTime), иначе false
 */
function isObject(mixed $value): bool
{
    return is_object($value) && !($value instanceof \DateTime);
}

/**
 * Подготавливает значение для вывода
 *
 * @param mixed $value Значение для подготовки
 *
 * @return mixed Подготовленное значение
 */
function prepareValue(mixed $value): mixed
{
    return is_object($value) ? (array)$value : $value;
}

/**
 * Форматирует различия в указанный формат
 *
 * @param array $diff Массив различий
 * @param string $format Формат вывода (stylish, plain, json)
 *
 * @return string Отформатированные различия
 */
function formatDiff(array $diff, string $format): string
{
    switch ($format) {
        case 'plain':
            return PlainFormatter::format($diff);
        case 'json':
            return JsonFormatter::format($diff);
        case 'stylish':
        default:
            return StylishFormatter::format($diff);
    }
}