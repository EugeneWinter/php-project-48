<?php

declare(strict_types=1);

namespace Differ\Parsers\ParserFactory;

use Exception;

use function Differ\Parsers\JsonParser\parse as parseJson;
use function Differ\Parsers\JsonParser\supports as supportsJson;
use function Differ\Parsers\YamlParser\parse as parseYaml;
use function Differ\Parsers\YamlParser\supports as supportsYaml;

function getFormat(string $filePath): string
{
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    return match ($extension) {
        'json' => 'json',
        'yml', 'yaml' => 'yaml',
        default => throw new Exception(
            sprintf('Unsupported file extension: %s', $extension)
        ),
    };
}

function getParsers(): array
{
    return [
        [
            'supports' => supportsJson(...),
            'parse'    => parseJson(...),
        ],
        [
            'supports' => supportsYaml(...),
            'parse'    => parseYaml(...),
        ],
    ];
}

function parse(string $content, string $format): object
{
    $parsers = getParsers();

    $matched = array_filter(
        $parsers,
        fn(array $parser): bool => ($parser['supports'])($format)
    );

    $first = reset($matched);

    if ($first === false) {
        throw new Exception(sprintf('Unsupported format: %s', $format));
    }

    return ($first['parse'])($content);
}
