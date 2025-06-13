<?php

declare(strict_types=1);

namespace Differ\Parsers;

use Exception;
use stdClass;
use Symfony\Component\Yaml\Yaml;

function parse(string $content, string $format): stdClass
{
    return match ($format) {
        'json' => parseJson($content),
        'yaml', 'yml' => parseYaml($content),
        default => throw new Exception("Unsupported format: '{$format}'"),
    };
}

function parseJson(string $content): stdClass
{
    try {
        if (trim($content) === '') {
            return new stdClass();
        }

        $data = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        return is_object($data) ? $data : (object) $data;
    } catch (\JsonException $e) {
        throw new Exception("JSON parse error: {$e->getMessage()}");
    }
}

function parseYaml(string $content): stdClass
{
    try {
        if (trim($content) === '') {
            return new stdClass();
        }

        $data = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        return is_object($data) ? $data : (object) $data;
    } catch (\Exception $e) {
        throw new Exception("YAML parse error: {$e->getMessage()}");
    }
}

function getSupportedFormats(): array
{
    return ['json', 'yaml', 'yml'];
}
