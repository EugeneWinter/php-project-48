<?php

namespace DiffGenerator\Tests\Formatters;

use DiffGenerator\Formatters\JsonFormatter;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $diff = [
            [
                'type' => 'changed',
                'key' => 'timeout',
                'oldValue' => 50,
                'newValue' => 20
            ],
            [
                'type' => 'nested',
                'key' => 'settings',
                'children' => [
                    [
                        'type' => 'added',
                        'key' => 'log',
                        'value' => true
                    ]
                ]
            ]
        ];

        $expected = <<<JSON
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

        $this->assertJsonStringEqualsJsonString(
            $expected,
            JsonFormatter::format($diff)
        );
    }
}