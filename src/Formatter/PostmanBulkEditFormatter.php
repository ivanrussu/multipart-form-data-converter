<?php

namespace IvanRussu\MultipartFormDataConverter\Formatter;

class PostmanBulkEditFormatter implements Formatter
{
    public function format($array): string
    {
        return implode(PHP_EOL, $this->_format($array));
    }

    private function _format(array $array, array $key_override = []): array
    {
        $res = [];

        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $key_write = $key;

                if (!empty($key_override)) {
                    $key_write = $key_override[0];
                    for ($i = 1, $iMax = count($key_override); $i < $iMax; $i++) {
                        $key_write .= '[' . $key_override[$i] . ']';
                    }

                    $key_write .= '[' . $key . ']';
                }

                $res[] = $key_write . ': ' . $value;
                continue;
            }

            $res = [...$res, ...$this->_format($value, [...$key_override, $key])];
        }

        return $res;
    }
}
