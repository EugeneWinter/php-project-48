<?php

namespace Differ\Tests\Parsers;

use Differ\Parsers\JsonParser;
use PHPUnit\Framework\TestCase;
use Exception;

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
        $result = JsonParser::parse($json);

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

        JsonParser::parse('invalid json');
    }

    /**
     * Тестирует определение поддерживаемых форматов
     *
     * @return void
     */
    public function testSupportsJsonFormat(): void
    {
        $this->assertTrue(JsonParser::supports('json'));
        $this->assertFalse(JsonParser::supports('yaml'));
        $this->assertFalse(JsonParser::supports('xml'));
    }
}
