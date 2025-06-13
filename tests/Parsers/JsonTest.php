<?php

namespace Differ\Tests\Parsers;

use PHPUnit\Framework\TestCase;
use Exception;
use function Differ\Parsers\parse;
use function Differ\Parsers\getSupportedFormats;

class JsonTest extends TestCase
{
    public function testParseValidJson(): void
    {
        $json = '{"key":"value"}';
        $result = parse($json, 'json');

        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
    }

    public function testParseInvalidJson(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/JSON parse error/');

        parse('invalid json', 'json');
    }

    public function testSupportsJsonFormat(): void
    {
        $formats = getSupportedFormats();
        $this->assertContains('json', $formats);
        $this->assertNotContains('xml', $formats);
    }
}
