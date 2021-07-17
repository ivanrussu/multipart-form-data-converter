<?php

namespace IvanRussu\MultipartFormDataConverter;

class Parameter
{
    private string $key;

    private string $value;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getArrayKey(): string
    {
        return explode('[', $this->key)[0];
    }

    public function isArray(): bool
    {
        return preg_match(
                "/^(?'key'[^\[\]]+)(\[[^\[\]]*\])+$/",
                $this->key
            ) === 1;
    }

    public function getArrayKeys(): array
    {
        return array_map(
            static fn(string $i) => rtrim($i, ']'),
            array_slice(
                explode(
                    '[',
                    $this->key
                ),
                1
            )
        );
    }
}