<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml;
use Exception;

/**
 * Парсер YAML файлов
 *
 * Реализует преобразование YAML-содержимого в объекты PHP
 *
 * @category DiffGenerator
 * @package  Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class YamlParser implements ParserInterface
{
    /**
     * Преобразует YAML-содержимое в объект PHP
     *
     * @param string $content YAML-содержимое для парсинга
     *
     * @return object Результат парсинга в виде объекта
     *
     * @throws Exception При ошибках парсинга YAML
     */
    public static function parse(string $content): object
    {
        try {
            $result = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
            return $result ?? new \stdClass(); // Всегда возвращаем объект
        } catch (Exception $e) {
            throw new Exception(
                sprintf('YAML parse error: %s', $e->getMessage())
            );
        }
    }

    /**
     * Проверяет поддержку указанного формата
     *
     * @param string $format Проверяемый формат файла
     *
     * @return bool Возвращает true для форматов 'yaml' и 'yml'
     */
    public static function supports(string $format): bool
    {
        return in_array($format, ['yaml', 'yml'], true);
    }
}
