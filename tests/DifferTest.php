<?php

namespace DiffGenerator\Tests;

use PHPUnit\Framework\TestCase;
use function DiffGenerator\genDiff;

class DifferTest extends TestCase
{
    public function testFlatJsonDiff()
    {
        $expected = <<<EOD
{
  - follow: false
    host: hexlet.io
  - proxy: 123.234.53.22
  - timeout: 50
  + timeout: 20
  + verbose: true
}
EOD;

        $actual = genDiff(
            __DIR__ . '/fixtures/file1.json',
            __DIR__ . '/fixtures/file2.json'
        );

        $this->assertEquals(trim($expected), trim($actual));
    }
}