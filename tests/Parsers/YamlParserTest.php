<?php

namespace DiffGenerator\Tests\Parsers;

use DiffGenerator\Parsers\YamlParser;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Тесты для YamlParser
 *
 * Проверяет корректность парсинга YAML-данных и определение поддерживаемых форматов
 *
 * @category DiffGenerator
 * @package  Tests\Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class YamlParserTest extends TestCase
{
    /**
     * Тестирует парсинг валидного YAML-документа
     *
     * @return void
     */
    public function testParseValidYaml(): void
    {
        $yaml = <<<YAML
        key: value
        nested:
          item1: value1
          item2: 123
        YAML;

        $result = YamlParser::parse($yaml);

        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
        $this->assertEquals('value1', $result->nested->item1);
        $this->assertEquals(123, $result->nested->item2);
    }

    /**
     * Тестирует обработку невалидного YAML
     *
     * @return void
     */
    public function testParseInvalidYaml(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('YAML parse error');

        YamlParser::parse("key: [value");
    }

    /**
     * Тестирует определение поддерживаемых форматов
     *
     * @return void
     */
    public function testSupportsYamlFormats(): void
    {
        $this->assertTrue(YamlParser::supports('yaml'));
        $this->assertTrue(YamlParser::supports('yml'));
        $this->assertFalse(YamlParser::supports('json'));
        $this->assertFalse(YamlParser::supports('xml'));
    }

    /**
     * Тестирует парсинг пустого YAML-документа
     *
     * @return void
     */
    public function testParseEmptyYaml(): void
    {
        $result = YamlParser::parse('');
        $this->assertIsObject($result);
        $this->assertEmpty(get_object_vars($result));
    }
}
