<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;

use function Differ\Formatters\format;

class JsonTest extends TestCase
{
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

        $actual = format($diff, 'json');

        $this->assertJsonStringEqualsJsonString($expected, $actual);
        $this->assertJson($actual);
    }

    public function testFormatIsPrettyPrinted(): void
    {
        $diff = [
            [
                'type' => 'unchanged',
                'key' => 'simple',
                'value' => 'value',
            ],
        ];

        $result = format($diff, 'json');

        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString("    ", $result);
    }
}
