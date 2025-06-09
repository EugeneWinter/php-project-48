<?php

declare(strict_types=1);

namespace Differ\Parsers\JsonParser;

use Exception;

/**
 * Преобразует JSON-содержимое в объект
 *
 * @param string $content JSON-содержимое для парсинга
 *
 * @return object Распарсенные данные в виде объекта
 *
 * @throws Exception В случае ошибки парсинга JSON
 */
function parse(string $content): object
{
    $data = json_decode($content);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(
            sprintf('JSON parse error: %s', json_last_error_msg())
        );
    }

    return $data;
}

/**
 * Проверяет поддержку указанного формата
 *
 * @param string $format Формат файла для проверки
 *
 * @return bool True если формат поддерживается
 */
function supports(string $format): bool
{
    return $format === 'json';
}
