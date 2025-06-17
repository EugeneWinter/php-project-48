<?php

namespace Differ\Formatters\Json;

function format(array $diff): string
{
    $result = json_encode($diff, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return $result === false ? throw new \RuntimeException('JSON encode error') : $result;
}
