<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;

use function Differ\Formatters\JsonFormatter\formatJson;

/**
 * Тесты для JsonFormatter
 */
class JsonFormatterTest extends TestCase
{
    /**
     * Тестирует форматирование различий в JSON
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

        $actual = formatJson($diff);

        $this->assertJsonStringEqualsJsonString($expected, $actual);
        $this->assertJson($actual);
    }

    /**
     * Проверяет красивое форматирование JSON
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

        $result = formatJson($diff);

        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString("    ", $result);
    }
}
