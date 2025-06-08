<?php

namespace Differ\Parsers;

/**
 * Интерфейс для парсеров файлов
 *
 * Определяет контракт для классов, реализующих парсинг различных форматов файлов
 *
 * @category DiffGenerator  # Категория (оставлено по стандарту)
 * @package  Parsers       # Пакет (оставлено по стандарту)
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
interface ParserInterface
{
    /**
     * Преобразует содержимое файла в объект PHP
     *
     * @param string $content Содержимое файла для парсинга
     *
     * @return object Результат парсинга в виде объекта
     */
    public static function parse(string $content): object;

    /**
     * Проверяет поддержку указанного формата
     *
     * @param string $format Проверяемый формат файла
     *
     * @return bool Возвращает true, если формат поддерживается
     */
    public static function supports(string $format): bool;
}
