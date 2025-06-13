<?php

namespace Differ\Formatters;

use RuntimeException;

use function Differ\Formatters\Json\format as formatJson;
use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Stylish\format as formatStylish;

function format(array $diff, string $formatName): string
{
    return match ($formatName) {
        'stylish' => formatStylish($diff),
        'plain' => formatPlain($diff),
        'json' => formatJson($diff),
        default => throw new RuntimeException("Unknown format: '{$formatName}'")
    };
}
