<?php

declare(strict_types=1);

namespace Differ\Parsers\YamlParser;

use Exception;
use Symfony\Component\Yaml\Yaml;
use stdClass;

function supports(string $format): bool
{
    return in_array($format, ['yaml', 'yml'], true);
}

function parse(string $content): stdClass
{
    try {
        if (trim($content) === '') {
            return new stdClass();
        }

        $data = Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
        if (!is_object($data)) {
            throw new Exception('YAML must represent an object');
        }

        if (!$data instanceof stdClass) {
            $data = (object) (array) $data;
        }

        return $data;
    } catch (Exception $e) {
        throw new Exception("YAML parse error: {$e->getMessage()}");
    }
}
