<?php

namespace DiffGenerator\Tests\Formatters;

use DiffGenerator\Formatters\PlainFormatter;
use PHPUnit\Framework\TestCase;

class PlainFormatterTest extends TestCase
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

        $expected = <<<TEXT
Property 'timeout' was updated. From 50 to 20
Property 'settings.log' was added with value: true
TEXT;

        $this->assertEquals($expected, PlainFormatter::format($diff));
    }
}