<?php

namespace Differ\Tests\Parsers;

use PHPUnit\Framework\TestCase;
use Exception;

// Импортируем функции вместо класса
use function Differ\Parsers\JsonParser\parse;
use function Differ\Parsers\JsonParser\supports;

/**
 * Тесты для JsonParser
 *
 * Проверяет корректность парсинга JSON-данных
 *
 * @category DiffGenerator
 * @package  Tests\Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class JsonParserTest extends TestCase
{
    /**
     * Тестирует парсинг валидного JSON
     *
     * @return void
     */
    public function testParseValidJson(): void
    {
        $json = '{"key":"value"}';
        $result = parse($json); // Используем функцию вместо метода класса

        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
    }

    /**
     * Тестирует обработку невалидного JSON
     *
     * @return void
     */
    public function testParseInvalidJson(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/JSON parse error/');

        parse('invalid json'); // Используем функцию вместо метода класса
    }

    /**
     * Тестирует определение поддерживаемых форматов
     *
     * @return void
     */
    public function testSupportsJsonFormat(): void
    {
        $this->assertTrue(supports('json')); // Используем функцию вместо метода класса
        $this->assertFalse(supports('yaml'));
        $this->assertFalse(supports('xml'));
    }
}
