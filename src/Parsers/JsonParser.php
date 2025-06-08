<?php

namespace Differ\Parsers;

use Exception;

/**
 * Парсер JSON файлов
 *
 * @category DiffGenerator
 * @package  Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class JsonParser implements ParserInterface
{
    /**
     * Преобразует JSON-содержимое в объект
     *
     * @param string $content JSON-содержимое для парсинга
     *
     * @return object Распарсенные данные в виде объекта
     *
     * @throws Exception В случае ошибки парсинга JSON
     */
    public static function parse(string $content): object
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
    public static function supports(string $format): bool
    {
        return $format === 'json';
    }
}
