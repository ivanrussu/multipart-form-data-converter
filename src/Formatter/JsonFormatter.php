<?php

namespace IvanRussu\MultipartFormDataConverter\Formatter;

use IvanRussu\MultipartFormDataConverter\Exceptions\FormatException;

class JsonFormatter implements Formatter
{
    public function format($array): string
    {
        try {
            return json_encode($array, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new FormatException('Cannot format as JSON', 0, $e);
        }
    }
}
