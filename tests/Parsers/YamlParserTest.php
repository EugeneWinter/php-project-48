<?php

namespace DiffGenerator\Tests\Parsers;

use DiffGenerator\Parsers\YamlParser;
use PHPUnit\Framework\TestCase;

class YamlParserTest extends TestCase
{
    public function testParseValidYaml(): void
    {
        $yaml = "key: value";
        $result = YamlParser::parse($yaml);
        $this->assertEquals('value', $result->key);
    }

    public function testParseInvalidYaml(): void
    {
        $this->expectException(\Exception::class);
        YamlParser::parse("key: [value");
    }

    public function testSupportsYamlFormats(): void
    {
        $this->assertTrue(YamlParser::supports('yaml'));
        $this->assertTrue(YamlParser::supports('yml'));
        $this->assertFalse(YamlParser::supports('json'));
    }
}