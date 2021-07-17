<?php

namespace IvanRussu\MultipartFormDataConverter\Formatter;

interface Formatter
{
    public function format($array): string;
}
