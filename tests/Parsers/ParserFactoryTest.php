<?php

namespace DiffGenerator\Tests\Parsers;

use DiffGenerator\Parsers\ParserFactory;
use PHPUnit\Framework\TestCase;

class ParserFactoryTest extends TestCase
{
    public function testGetFormat(): void
    {
        $this->assertEquals('json', ParserFactory::getFormat('/path/to/file.json'));
        $this->assertEquals('yaml', ParserFactory::getFormat('/path/to/file.yaml'));
        $this->assertEquals('yaml', ParserFactory::getFormat('/path/to/file.yml'));
    }

    public function testGetFormatWithUnsupportedExtension(): void
    {
        $this->expectException(\Exception::class);
        ParserFactory::getFormat('/path/to/file.txt');
    }

    public function testParseWithDifferentFormats(): void
    {
        $json = '{"key":"value"}';
        $result = ParserFactory::parse($json, 'json');
        $this->assertEquals('value', $result->key);

        $yaml = "key: value";
        $result = ParserFactory::parse($yaml, 'yaml');
        $this->assertEquals('value', $result->key);
    }
}