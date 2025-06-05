<?php

/**
 * Тесты для генератора различий между файлами
 */

namespace DiffGenerator\Tests;

use function DiffGenerator\genDiff;
use PHPUnit\Framework\TestCase;

/**
 * Класс для тестирования генератора различий
 */
class DifferTest extends TestCase
{
    /**
     * Тест для плоских JSON файлов
     */
    public function testFlatJsonDiff(): void
    {
        $expected = <<<TEXT
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
TEXT;

        $actual = genDiff(
            __DIR__ . '/fixtures/file1.json',
            __DIR__ . '/fixtures/file2.json'
        );
        $this->assertEquals(trim($expected), trim($actual));
    }

    /**
     * Тест для плоских YAML файлов
     */
    public function testFlatYamlDiff(): void
    {
        $expected = <<<TEXT
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
TEXT;

        $actual = genDiff(
            __DIR__ . '/fixtures/file1.yaml',
            __DIR__ . '/fixtures/file2.yaml'
        );
        $this->assertEquals(trim($expected), trim($actual));
    }

    /**
     * Тест для вложенных JSON файлов
     */
    public function testNestedJsonDiff(): void
    {
        $expected = <<<TEXT
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            key: value
          + ops: vops
            doge: {
              - wow: 
              + wow: so much
            }
        }
    }
}
TEXT;

        $actual = genDiff(
            __DIR__ . '/fixtures/nested1.json',
            __DIR__ . '/fixtures/nested2.json'
        );
        $this->assertEquals(trim($expected), trim($actual));
    }

    /**
     * Тест для вложенных YAML файлов
     */
    public function testNestedYamlDiff(): void
    {
        $expected = <<<TEXT
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            key: value
          + ops: vops
            doge: {
              - wow: 
              + wow: so much
            }
        }
    }
}
TEXT;

        $actual = genDiff(
            __DIR__ . '/fixtures/nested1.yaml',
            __DIR__ . '/fixtures/nested2.yaml'
        );
        $this->assertEquals(trim($expected), trim($actual));
    }

    /**
     * Тест для plain формата вывода
     */
    public function testPlainFormat(): void
    {
        $expected = implode("\n", [
            "Property 'common.follow' was added with value: false",
            "Property 'common.setting2' was removed",
            "Property 'common.setting3' was updated. From true to null",
            "Property 'common.setting4' was added with value: 'blah blah'",
            "Property 'common.setting5' was added with value: [complex value]",
            "Property 'common.setting6.doge.wow' was updated. From '' to 'so much'",
            "Property 'common.setting6.ops' was added with value: 'vops'"
        ]);

        $actual = genDiff(
            __DIR__ . '/fixtures/nested1.json',
            __DIR__ . '/fixtures/nested2.json',
            'plain'
        );
        
        $this->assertEquals($expected, $actual);
    }

    /**
     * Тест для JSON формата вывода
     */
    public function testJsonFormat(): void
    {
        $actual = genDiff(
            __DIR__ . '/fixtures/file1.json',
            __DIR__ . '/fixtures/file2.json',
            'json'
        );
        $this->assertJson($actual);
        $decoded = json_decode($actual, true);
        $this->assertArrayHasKey('timeout', $decoded);
        $this->assertEquals(50, $decoded['timeout']['oldValue']);
        $this->assertEquals(20, $decoded['timeout']['newValue']);
    }

    /**
     * Тест для случая, когда файл не найден
     */
    public function testFileNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        genDiff('nonexistent.json', __DIR__ . '/fixtures/file1.json');
    }

    /**
     * Тест для случая разных форматов файлов
     */
    public function testDifferentFormats(): void
    {
        $this->expectException(\RuntimeException::class);
        genDiff(
            __DIR__ . '/fixtures/file1.json',
            __DIR__ . '/fixtures/file1.yaml'
        );
    }
}