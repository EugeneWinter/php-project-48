<?php

declare(strict_types=1);

namespace Differ\Parsers\YamlParser;

use Symfony\Component\Yaml\Yaml;
use Exception;

function supports(string $format): bool
{
    return in_array($format, ['yaml', 'yml'], true);
}

function parse(string $content): object
{
    try {
        $result = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        return $result ?? new \stdClass();
    } catch (Exception $e) {
        throw new Exception(
            sprintf('YAML parse error: %s', $e->getMessage())
        );
    }
}
