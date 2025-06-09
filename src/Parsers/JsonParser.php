<?php

declare(strict_types=1);

namespace Differ\Parsers\JsonParser;

use Exception;

function parse(string $content): object
{
    $data = json_decode($content);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception(
            sprintf('JSON parse error: %s', json_last_error_msg())
        );
    }

    return $data;
}

function supports(string $format): bool
{
    return $format === 'json';
}
