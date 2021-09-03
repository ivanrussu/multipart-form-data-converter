<?php

namespace IvanRussu\MultipartFormDataConverter;

class MultipartMessage
{
    private string $multipart;
    private array $lastNumericKeys = [];
    private array $result = [];

    public function __construct(string $multipart)
    {
        $this->multipart = $multipart;
    }

    public function parse(): array
    {
        $this->result = [];
        $this->lastNumericKeys = [];
        foreach ($this->explodeByParameters() as $parameter) {
            $this->handleParameter($parameter);
        }
        return $this->result;
    }

    private function explodeByParameters(): array
    {
        preg_match_all(
            '/((?:name=\"(?\'name\'[^\"]*)\")(?:[\r\n])+(?\'value\'.*))/',
            $this->multipart,
            $matches,
            PREG_SET_ORDER
        );

        return array_map(
            static fn($match) => new Parameter($match['name'], $match['value'] ?? ''),
            $matches
        );
    }

    private function getRealKey(Parameter $parameter, array $keys, string $partKey): string|int
    {
        if ($partKey !== '') {
            return $partKey;
        }

        $arrLastIndicesKey = $parameter->getArrayKey();

        foreach ($keys as $key) {
            $arrLastIndicesKey .= '[' . $key . ']';
        }

        if (!isset($this->lastNumericKeys[$arrLastIndicesKey])) {
            $this->lastNumericKeys[$arrLastIndicesKey] = -1;
        }
        $numericKey = $this->lastNumericKeys[$arrLastIndicesKey] + 1;
        $this->lastNumericKeys[$arrLastIndicesKey] = $numericKey;
        return $numericKey;
    }

    private function handleParameter(Parameter $parameter): void
    {
        if (!$parameter->isArray()) {
            $this->result[$parameter->getKey()] = $parameter->getValue();
            return;
        }
    
        $arr = $this->parseArray($parameter);
        $key = $parameter->getArrayKey();
        if (isset($this->result[$key])) {
            $sameKeyUsedForNonArrayValue = !is_array($this->result[$key]);
            if ($sameKeyUsedForNonArrayValue) {
                $this->result[$key] = [];
            }
            $this->result[$key] = $this->mergeArraysWithoutOverwritingNumericKeys($this->result[$key], $arr);
            return;
        }
    
        $this->result[$key] = $arr;
    }
    
    /**
     * Slightly modified version of https://www.php.net/manual/en/function.array-merge-recursive.php#106985
     *
     * @param ...$arrays
     * @return mixed|null
     */
    private function mergeArraysWithoutOverwritingNumericKeys(...$arrays) {
        $base = array_shift($arrays);
        foreach ($arrays as $array) {
            reset($base);
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                    $base[$key] = $this->mergeArraysWithoutOverwritingNumericKeys($base[$key], $value);
                    continue;
                }
                
                $base[$key] = $value;
            }
        }
        
        return $base;
    }

    private function parseArray(Parameter $parameter)
    {
        $arrayKeys = $parameter->getArrayKeys();

        $result = [];
        $realKeys = [];

        $last_index = count($arrayKeys) - 1;
        foreach ($arrayKeys as $index => $key) {
            $realKeys[] = $this->getRealKey($parameter, $arrayKeys, $key);

            $previousLevel = &$result;
            foreach ($realKeys as $realKey) {
                if (!isset($previousLevel[$realKey])) {
                    $previousLevel[$realKey] = [];
                }
                $previousLevel = &$previousLevel[$realKey];
            }

            if ($last_index === $index) {
                $previousLevel = $parameter->getValue();
            }
        }

        return $result;
    }
}
