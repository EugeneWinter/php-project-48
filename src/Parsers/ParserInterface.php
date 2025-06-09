<?php

namespace Differ\Parsers;

interface ParserInterface
{
    public static function parse(string $content): object;
    public static function supports(string $format): bool;
}
