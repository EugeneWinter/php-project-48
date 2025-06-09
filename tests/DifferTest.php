<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Differ\genDiff;

$testFlatJsonDiff = function () {
    $expected = <<<TEXT
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
TEXT;

    $actual = genDiff(
        __DIR__ . '/fixtures/file1.json',
        __DIR__ . '/fixtures/file2.json'
    );

    $this->assertEquals(trim($expected), trim($actual));
};

$testFlatYamlDiff = function () {
    $expected = <<<TEXT
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
TEXT;

    $actual = genDiff(
        __DIR__ . '/fixtures/file1.yaml',
        __DIR__ . '/fixtures/file2.yaml'
    );

    $this->assertEquals(trim($expected), trim($actual));
};

$testNestedJsonDiff = function () {
    $expected = <<<TEXT
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            key: value
          + ops: vops
            doge: {
              - wow: 
              + wow: so much
            }
        }
    }
}
TEXT;

    $actual = genDiff(
        __DIR__ . '/fixtures/nested1.json',
        __DIR__ . '/fixtures/nested2.json'
    );

    $this->assertEquals(trim($expected), trim($actual));
};

$testNestedYamlDiff = function () {
    $expected = <<<TEXT
{
    common: {
      + follow: false
        setting1: Value 1
      - setting2: 200
      - setting3: true
      + setting3: null
      + setting4: blah blah
      + setting5: {
            key5: value5
        }
        setting6: {
            key: value
          + ops: vops
            doge: {
              - wow: 
              + wow: so much
            }
        }
    }
}
TEXT;

    $actual = genDiff(
        __DIR__ . '/fixtures/nested1.yaml',
        __DIR__ . '/fixtures/nested2.yaml'
    );

    $this->assertEquals(trim($expected), trim($actual));
};

$testPlainFormat = function () {
    $expected = implode("\n", [
        "Property 'common.follow' was added with value: false",
        "Property 'common.setting2' was removed",
        "Property 'common.setting3' was updated. From true to null",
        "Property 'common.setting4' was added with value: 'blah blah'",
        "Property 'common.setting5' was added with value: [complex value]",
        "Property 'common.setting6.doge.wow' was updated. From '' to 'so much'",
        "Property 'common.setting6.ops' was added with value: 'vops'"
    ]);

    $actual = genDiff(
        __DIR__ . '/fixtures/nested1.json',
        __DIR__ . '/fixtures/nested2.json',
        'plain'
    );

    $this->assertEquals($expected, $actual);
};

$testJsonFormat = function () {
    $actual = genDiff(
        __DIR__ . '/fixtures/file1.json',
        __DIR__ . '/fixtures/file2.json',
        'json'
    );

    $this->assertJson($actual);

    $decoded = json_decode($actual, true);
    $this->assertIsArray($decoded);
    $this->assertArrayHasKey('timeout', $decoded);
    $this->assertEquals(50, $decoded['timeout']['oldValue']);
    $this->assertEquals(20, $decoded['timeout']['newValue']);
};

$testFileNotFound = function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('File not found: nonexistent.json');

    genDiff('nonexistent.json', __DIR__ . '/fixtures/file1.json');
};

$testDifferentFormats = function () {
    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('Different file formats: json and yaml');

    genDiff(
        __DIR__ . '/fixtures/file1.json',
        __DIR__ . '/fixtures/file1.yaml'
    );
};