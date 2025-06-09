<?php

declare(strict_types=1);

namespace Differ\Parsers\JsonParser;

use Exception;
use stdClass;

function supports(string $format): bool
{
    return $format === 'json';
}

function parse(string $content): stdClass
{
    try {
        $data = json_decode($content, false, 512, JSON_THROW_ON_ERROR);
        if (!is_object($data)) {
            throw new Exception('JSON must represent an object');
        }
        return $data;
    } catch (Exception $e) {
        throw new Exception("JSON parse error: {$e->getMessage()}");
    }
}
