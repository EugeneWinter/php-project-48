<?php

declare(strict_types=1);

namespace Differ\Parsers\YamlParser;

use Symfony\Component\Yaml\Yaml;
use Exception;

/**
 * Проверяет поддержку указанного формата YAML
 *
 * @param string $format
 * @return bool
 */
function supports(string $format): bool
{
    return in_array($format, ['yaml', 'yml'], true);
}

/**
 * Парсит YAML-содержимое в объект PHP
 *
 * @param string $content
 * @return object
 * @throws Exception
 */
function parse(string $content): object
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
