<?php

namespace DiffGenerator;

function genDiff(string $path1, string $path2): string
{
    if (empty($path1) || empty($path2)) {
        throw new \InvalidArgumentException("File paths cannot be empty");
    }
    
    $data1 = (array)parseFile($path1);
    $data2 = (array)parseFile($path2);
    
    $keys = array_unique(array_merge(
        array_keys($data1),
        array_keys($data2)
    ));
    sort($keys);

    $lines = [];
    foreach ($keys as $key) {
        $value1 = $data1[$key] ?? null;
        $value2 = $data2[$key] ?? null;

        if (!array_key_exists($key, $data1)) {
            $lines[] = "  + $key: " . stringifyValue($value2);
            continue;
        }
        
        if (!array_key_exists($key, $data2)) {
            $lines[] = "  - $key: " . stringifyValue($value1);
            continue;
        }
        
        if ($value1 === $value2) {
            $lines[] = "    $key: " . stringifyValue($value1);
            continue;
        }
        
        $lines[] = "  - $key: " . stringifyValue($value1);
        $lines[] = "  + $key: " . stringifyValue($value2);
    }

    return "{\n" . implode("\n", $lines) . "\n}";
}

function stringifyValue(mixed $value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE);
}

function parseFile(string $path): object
{
    $content = file_get_contents($path);
    if ($content === false) {
        throw new \RuntimeException("Failed to read file: {$path}");
    }
    
    $data = json_decode($content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException("JSON parse error: " . json_last_error_msg());
    }
    
    return $data;
}