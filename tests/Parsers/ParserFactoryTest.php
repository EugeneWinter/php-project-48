<?php

namespace Differ\Tests\Parsers;

use function Differ\Parsers\getFormat;
use function Differ\Parsers\parse;
use Exception;
function run_parser_tests()
{
    testGetFormat();
    testGetFormatWithUnsupportedExtension();
    testParseWithDifferentFormats();
    testParseWithUnsupportedFormat();
    
    echo "All parser tests passed!\n";
}

function testGetFormat()
{
    assert('json' === getFormat('/path/to/file.json'));
    assert('yaml' === getFormat('/path/to/file.yaml'));
    assert('yaml' === getFormat('/path/to/file.yml'));
}

function testGetFormatWithUnsupportedExtension()
{
    $exceptionThrown = false;
    
    try {
        getFormat('/path/to/file.txt');
    } catch (Exception $e) {
        $exceptionThrown = true;
        assert($e instanceof Exception);
        assert(str_contains($e->getMessage(), 'Unsupported file extension: txt'));
    }
    
    assert($exceptionThrown, 'Expected exception was not thrown');
}

function testParseWithDifferentFormats()
{
    $json = '{"key":"value"}';
    $result = parse($json, 'json');
    assert(is_object($result));
    assert('value' === $result->key);

    $yaml = "key: value";
    $result = parse($yaml, 'yaml');
    assert(is_object($result));
    assert('value' === $result->key);
}

function testParseWithUnsupportedFormat()
{
    $exceptionThrown = false;
    
    try {
        parse('<xml>value</xml>', 'xml');
    } catch (Exception $e) {
        $exceptionThrown = true;
        assert($e instanceof Exception);
        assert(str_contains($e->getMessage(), 'Unsupported format: xml'));
    }
    
    assert($exceptionThrown, 'Expected exception was not thrown');
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    run_parser_tests();
}