<?php

namespace Differ\Tests\Parsers;

use Differ\Parsers\ParserFactory;
use PHPUnit\Framework\TestCase;
use Exception;

/**
 * Тесты для ParserFactory
 *
 * Проверяет работу фабрики парсеров для разных форматов файлов
 *
 * @category DiffGenerator
 * @package  Tests\Parsers
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class ParserFactoryTest extends TestCase
{
    /**
     * Тестирует определение формата по расширению файла
     *
     * @return void
     */
    public function testGetFormat(): void
    {
        $this->assertEquals('json', ParserFactory::getFormat('/path/to/file.json'));
        $this->assertEquals('yaml', ParserFactory::getFormat('/path/to/file.yaml'));
        $this->assertEquals('yaml', ParserFactory::getFormat('/path/to/file.yml'));
    }

    /**
     * Тестирует обработку неподдерживаемых расширений файлов
     *
     * @return void
     */
    public function testGetFormatWithUnsupportedExtension(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported file extension: txt');

        ParserFactory::getFormat('/path/to/file.txt');
    }

    /**
     * Тестирует парсинг разных форматов данных
     *
     * @return void
     */
    public function testParseWithDifferentFormats(): void
    {
        // Тестирование JSON
        $json = '{"key":"value"}';
        $result = ParserFactory::parse($json, 'json');
        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);

        // Тестирование YAML
        $yaml = "key: value";
        $result = ParserFactory::parse($yaml, 'yaml');
        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
    }

    /**
     * Тестирует обработку неизвестного формата данных
     *
     * @return void
     */
    public function testParseWithUnsupportedFormat(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unsupported format: xml');

        ParserFactory::parse('<xml>value</xml>', 'xml');
    }
}
