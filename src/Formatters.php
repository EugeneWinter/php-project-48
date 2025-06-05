<?php

namespace DiffGenerator;

use DiffGenerator\Formatters\{
    JsonFormatter,
    PlainFormatter,
    StylishFormatter
};
function getFormatter(string $format): callable
{
    return match ($format) {
        'json' => fn($diff) => JsonFormatter::format($diff),
        'plain' => fn($diff) => PlainFormatter::format($diff),
        'stylish' => fn($diff) => StylishFormatter::format($diff),
        default => throw new \InvalidArgumentException("Unknown format: {$format}")
    };
}