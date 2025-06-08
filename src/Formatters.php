<?php

namespace Differ;

use Differ\Formatters\{
    JsonFormatter,
    PlainFormatter,
    StylishFormatter
};
use InvalidArgumentException;

/**
 * Возвращает подходящий форматтер для указанного формата вывода
 *
 * @param string $format Формат вывода ('json', 'plain' или 'stylish')
 *
 * @return callable Функция-форматтер, принимающая массив различий
 *
 * @throws InvalidArgumentException При передаче неизвестного формата
 */
function getFormatter(string $format): callable
{
    return match ($format) {
        'json' => fn(array $diff): string => JsonFormatter::format($diff),
        'plain' => fn(array $diff): string => PlainFormatter::format($diff),
        'stylish' => fn(array $diff): string => StylishFormatter::format($diff),
        default => throw new InvalidArgumentException(
            sprintf('Неизвестный формат: %s', $format)
        ),
    };
}
