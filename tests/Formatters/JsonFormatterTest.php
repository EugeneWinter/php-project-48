<?php

namespace Differ\Tests\Formatters;

use DiffGenerator\Formatters\JsonFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для JsonFormatter
 *
 * Проверяет корректность форматирования различий в JSON-формат
 *
 * @category DiffGenerator
 * @package  Tests\Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class JsonFormatterTest extends TestCase
{
    /**
     * Тестирует форматирование различий в JSON
     *
     * @return void
     */
    public function testFormat(): void
    {
        $diff = [
            [
                'type' => 'changed',
                'key' => 'timeout',
                'oldValue' => 50,
                'newValue' => 20,
            ],
            [
                'type' => 'nested',
                'key' => 'settings',
                'children' => [
                    [
                        'type' => 'added',
                        'key' => 'log',
                        'value' => true,
                    ],
                ],
            ],
        ];

        $expected = <<<'JSON'
{
    "timeout": {
        "type": "changed",
        "oldValue": 50,
        "newValue": 20
    },
    "settings": {
        "log": {
            "type": "added",
            "value": true
        }
    }
}
JSON;

        $actual = JsonFormatter::format($diff);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
        $this->assertJson($actual);
    }

    /**
     * Проверяет красивое форматирование JSON (с отступами)
     *
     * @return void
     */
    public function testFormatIsPrettyPrinted(): void
    {
        $diff = [
            [
                'type' => 'unchanged',
                'key' => 'simple',
                'value' => 'value',
            ],
        ];

        $result = JsonFormatter::format($diff);

        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString("    ", $result);
    }
}
