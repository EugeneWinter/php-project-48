<?php

namespace DiffGenerator;

/**
 * Класс или файл, содержащий форматтеры для диффов.
 * 
 * В этом файле определены форматтеры для различных форматов вывода.
 */
 
use DiffGenerator\Formatters\{
    JsonFormatter,
    PlainFormatter,
    StylishFormatter
};

/**
 * Возвращает функцию форматирования по заданному типу.
 *
 * @param string $format Тип формата ('json', 'plain', 'stylish').
 *
 * @return callable Функция форматирования.
 *
 * @throws \InvalidArgumentException Если формат неизвестен.
 */
function getFormatter(string $format): callable
{
    return match ($format) {
        'json' => fn($diff) => JsonFormatter::format($diff),
        'plain' => fn($diff) => PlainFormatter::format($diff),
        'stylish' => fn($diff) => StylishFormatter::format($diff),
        default => throw new \InvalidArgumentException("Unknown format: {$format}")
    };
}