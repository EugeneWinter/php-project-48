<?php

namespace Differ\Tests\Parsers;

use PHPUnit\Framework\TestCase;
use Exception;

use function Differ\Parsers\JsonParser\parse;
use function Differ\Parsers\JsonParser\supports;

class JsonParserTest extends TestCase
{
    public function testParseValidJson(): void
    {
        $json = '{"key":"value"}';
        $result = parse($json);

        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
    }

    public function testParseInvalidJson(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/JSON parse error/');

        parse('invalid json');
    }

    public function testSupportsJsonFormat(): void
    {
        $this->assertTrue(supports('json'));
        $this->assertFalse(supports('yaml'));
        $this->assertFalse(supports('xml'));
    }
}
