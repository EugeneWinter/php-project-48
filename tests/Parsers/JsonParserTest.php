<?php

namespace DiffGenerator\Tests\Parsers;

use DiffGenerator\Parsers\JsonParser;
use PHPUnit\Framework\TestCase;

class JsonParserTest extends TestCase
{
    public function testParseValidJson(): void
    {
        $json = '{"key":"value"}';
        $result = JsonParser::parse($json);
        $this->assertEquals('value', $result->key);
    }

    public function testParseInvalidJson(): void
    {
        $this->expectException(\Exception::class);
        JsonParser::parse('invalid json');
    }

    public function testSupportsJsonFormat(): void
    {
        $this->assertTrue(JsonParser::supports('json'));
        $this->assertFalse(JsonParser::supports('yaml'));
    }
}