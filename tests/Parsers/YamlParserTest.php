<?php

namespace Differ\Tests\Parsers;

use PHPUnit\Framework\TestCase;
use Exception;

use function Differ\Parsers\YamlParser\parse;
use function Differ\Parsers\YamlParser\supports;

class YamlParserTest extends TestCase
{
    public function testParseValidYaml(): void
    {
        $yaml = <<<YAML
key: value
nested:
  item1: value1
  item2: 123
YAML;

        $result = parse($yaml);

        $this->assertIsObject($result);
        $this->assertEquals('value', $result->key);
        $this->assertEquals('value1', $result->nested->item1);
        $this->assertEquals(123, $result->nested->item2);
    }

    public function testParseInvalidYaml(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/YAML parse error/');

        parse("key: [value");
    }

    public function testSupportsYamlFormats(): void
    {
        $this->assertTrue(supports('yaml'));
        $this->assertTrue(supports('yml'));
        $this->assertFalse(supports('json'));
        $this->assertFalse(supports('xml'));
    }

    public function testParseEmptyYaml(): void
    {
        $result = parse('');
        $this->assertIsObject($result);
        $this->assertEmpty(get_object_vars($result));
    }
}