<?php

namespace Differ\Tests\Parsers;

use function Differ\Parsers\YamlParser\parse;
use function Differ\Parsers\YamlParser\supports;
use Exception;

function run_yaml_parser_tests()
{
    testParseValidYaml();
    testParseInvalidYaml();
    testSupportsYamlFormats();
    testParseEmptyYaml();
    
    echo "All YAML parser tests passed!\n";
}

function testParseValidYaml()
{
    $yaml = <<<YAML
    key: value
    nested:
      item1: value1
      item2: 123
    YAML;

    $result = parse($yaml);

    assert(is_object($result));
    assert('value' === $result->key);
    assert('value1' === $result->nested->item1);
    assert(123 === $result->nested->item2);
}

function testParseInvalidYaml()
{
    $exceptionThrown = false;
    
    try {
        parse("key: [value");
    } catch (Exception $e) {
        $exceptionThrown = true;
        assert($e instanceof Exception);
        assert(str_contains($e->getMessage(), 'YAML parse error'));
    }
    
    assert($exceptionThrown, 'Expected exception was not thrown');
}

function testSupportsYamlFormats()
{
    assert(true === supports('yaml'));
    assert(true === supports('yml'));
    assert(false === supports('json'));
    assert(false === supports('xml'));
}

function testParseEmptyYaml()
{
    $result = parse('');
    assert(is_object($result));
    assert(empty(get_object_vars($result)));
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    run_yaml_parser_tests();
}