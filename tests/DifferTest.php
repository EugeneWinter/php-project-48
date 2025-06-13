<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;

use function Differ\Differ\genDiff;
use function Differ\Tests\getFixturePath;

class DifferTest extends TestCase
{
    public function fileProvider(): array
    {
        return [
            ['file1.json', 'file2.json', 'stylish', 'flat_stylish.txt'],
            ['file1.yaml', 'file2.yaml', 'stylish', 'flat_stylish.txt'],
            ['nested1.json', 'nested2.json', 'stylish', 'nested_stylish.txt'],
            ['nested1.yaml', 'nested2.yaml', 'stylish', 'nested_stylish.txt'],
            ['nested1.json', 'nested2.json', 'plain', 'plain.txt'],
            ['file1.json', 'file2.json', 'json', 'flat_json.txt']
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testGenDiff(string $file1, string $file2, string $format, string $expectedFile): void
    {
        $expected = $this->normalizeLineEndings(file_get_contents(getFixturePath($expectedFile)));
        $actual = $this->normalizeLineEndings(
            genDiff(
                getFixturePath($file1),
                getFixturePath($file2),
                $format
            )
        );
        $this->assertEquals($expected, $actual);
    }

    private function normalizeLineEndings(string $content): string
    {
        return str_replace("\r\n", "\n", $content);
    }

    public function testFileNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File not found: nonexistent.json');
        genDiff('nonexistent.json', getFixturePath('file1.json'));
    }

    public function testInvalidFileFormat(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported file extension: txt');
        genDiff(getFixturePath('invalid.txt'), getFixturePath('file1.json'));
    }
}
