<?php

namespace Differ\Parsers;

use Exception;

/**
 * Фабрика для создания парсеров на основе формата файла
 *
 * Определяет подходящий парсер и преобразует содержимое файла в объект PHP
 *
 * @category DiffGenerator
 * @package  Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class ParserFactory
{
    /** @var array<class-string> Список доступных классов парсеров */
    private static array $parsers = [
        JsonParser::class,
        YamlParser::class,
    ];

    /**
     * Определяет формат файла по его расширению
     *
     * @param string $filePath Путь к файлу
     *
     * @return string Определённый формат ('json' или 'yaml')
     *
     * @throws Exception Если расширение файла не поддерживается
     */
    public static function getFormat(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'json' => 'json',
            'yml', 'yaml' => 'yaml',
            default => throw new Exception(
                sprintf('Unsupported file extension: %s', $extension)
            ),
        };
    }

    /**
     * Парсит содержимое с помощью подходящего парсера
     *
     * @param string $content Содержимое для парсинга
     * @param string $format  Формат содержимого ('json' или 'yaml')
     *
     * @return object Распарсенные данные
     *
     * @throws Exception Если не найден подходящий парсер
     */
    public static function parse(string $content, string $format): object
    {
        foreach (self::$parsers as $parser) {
            if ($parser::supports($format)) {
                return $parser::parse($content);
            }
        }

        throw new Exception(
            sprintf('Unsupported format: %s', $format)
        );
    }
}
