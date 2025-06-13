<?php

namespace Differ\Tests\Formatters;

use PHPUnit\Framework\TestCase;
use function Differ\Formatters\format;

class PlainTest extends TestCase
{
    public function testFormat(): void
    {
        $diff = [
            [
                'type' => 'added',
                'key' => 'verbose',
                'value' => true
            ],
            [
                'type' => 'removed',
                'key' => 'proxy',
                'value' => '127.0.0.1'
            ],
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
                        'value' => ['file' => 'app.log']
                    ]
                ]
            ]
        ];

        $result = format($diff, 'plain');
        $lines = explode("\n", $result);

        $this->assertCount(4, $lines);
        $this->assertStringContainsString(
            "Property 'verbose' was added with value: true",
            $result
        );
        $this->assertStringContainsString(
            "Property 'proxy' was removed",
            $result
        );
        $this->assertStringContainsString(
            "Property 'timeout' was updated. From 50 to 20",
            $result
        );
        $this->assertStringContainsString(
            "Property 'settings.log' was added with value: [complex value]",
            $result
        );
    }
}
