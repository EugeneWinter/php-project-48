<?php

namespace DiffGenerator\Parsers;

use Exception;

class JsonParser implements ParserInterface
{
    public static function parse(string $content): object
    {
        $data = json_decode($content);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON parse error: " . json_last_error_msg());
        }
        
        return $data;
    }

    public static function supports(string $format): bool
    {
        return $format === 'json';
    }
}