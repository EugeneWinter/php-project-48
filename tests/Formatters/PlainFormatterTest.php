<?php

namespace DiffGenerator\Tests\Formatters;

use DiffGenerator\Formatters\PlainFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для PlainFormatter
 *
 * @category DiffGenerator
 * @package  Tests\Formatters
 * @author   Eugene Winter <corvoattano200529@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/EugeneWinter/php-project-48
 */
class PlainFormatterTest extends TestCase
{
    /**
     * Тестирование форматирования различий
     *
     * @return void
     */
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
                'value' => '123.234.53.22'
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

        $result = PlainFormatter::format($diff);
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
