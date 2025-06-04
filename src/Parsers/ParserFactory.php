<?php

namespace DiffGenerator\Parsers;

use Exception;

class ParserFactory
{
    private static array $parsers = [
        JsonParser::class,
        YamlParser::class
    ];

    public static function getFormat(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return match ($extension) {
            'json' => 'json',
            'yml', 'yaml' => 'yaml',
            default => throw new Exception("Unsupported file extension: {$extension}")
        };
    }

    public static function parse(string $content, string $format): object
    {
        foreach (self::$parsers as $parser) {
            if ($parser::supports($format)) {
                return $parser::parse($content);
            }
        }
        throw new Exception("Unsupported format: {$format}");
    }
}