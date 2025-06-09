<?php

namespace Differ;

use InvalidArgumentException;

use function Differ\Formatters\JsonFormatter\formatJson;
use function Differ\Formatters\PlainFormatter\formatPlain;
use function Differ\Formatters\StylishFormatter\formatStylish;

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
        'json' => fn(array $diff): string => formatJson($diff),
        'plain' => fn(array $diff): string => formatPlain($diff),
        'stylish' => fn(array $diff): string => formatStylish($diff),
        default => throw new InvalidArgumentException(
            sprintf('Неизвестный формат: %s', $format)
        ),
    };
}
