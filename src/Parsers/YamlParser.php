<?php

namespace DiffGenerator\Parsers;

use Symfony\Component\Yaml\Yaml;
use Exception;

class YamlParser implements ParserInterface
{
    public static function parse(string $content): object
    {
        try {
            return Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        } catch (Exception $e) {
            throw new Exception("YAML parse error: " . $e->getMessage());
        }
    }

    public static function supports(string $format): bool
    {
        return in_array($format, ['yaml', 'yml']);
    }
}