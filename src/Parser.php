<?php

namespace DiffGenerator;

function getFileContent(string $filePath): string
{
    if (!is_string($filePath) || empty($filePath)) {
        throw new \InvalidArgumentException('File path must be a non-empty string');
    }

    if (!file_exists($filePath)) {
        throw new \RuntimeException("File '{$filePath}' does not exist");
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        throw new \RuntimeException("Failed to read file '{$filePath}'");
    }

    return $content;
}

function parseFile(string $filePath): object
{
    $content = getFileContent($filePath);
    
    $data = json_decode($content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException("Failed to parse JSON: " . json_last_error_msg());
    }

    return $data;
}