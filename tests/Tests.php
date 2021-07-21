<?php

namespace IvanRussu\MultipartFormDataConverter\Tests;

use IvanRussu\MultipartFormDataConverter\Formatter\JsonFormatter;
use IvanRussu\MultipartFormDataConverter\Formatter\PostmanBulkEditFormatter;
use IvanRussu\MultipartFormDataConverter\MultipartMessage;
use PHPUnit\Framework\TestCase;

class Tests extends TestCase
{
    private string $test_string =
        <<<'MULTIPART'
        -----------------------------359001620640685356211451689597
        Content-Disposition: form-data; name="plain"

        1
        -----------------------------359001620640685356211451689597
        Content-Disposition: form-data; name="index_key[]"

        index_key_0
        -----------------------------359001620640685356211451689597
        Content-Disposition: form-data; name="index_key[]"

        index_key_1
        -----------------------------359001620640685356211451689597
        Content-Disposition: form-data; name="determined_index_key[4]"

        determined_index_key_4
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="string_key[a]"

        string_key_a
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="string_key[b]"

        string_key_b
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="string_key_index_key[c][]"

        string_key_index_key_c_0
        -----------------------------359001620640685356211451689597--
         Content-Disposition: form-data; name="string_key_index_key[c][]"

        string_key_index_key_c_1
        -----------------------------359001620640685356211451689597--
        MULTIPART;


    public function testCorrectArrayParsing(): void
    {
        $message = new MultipartMessage($this->test_string);
        $actual = $message->parse();

        $expected = [
            'plain'                => '1',
            'index_key'            => [
                'index_key_0',
                'index_key_1',
            ],
            'determined_index_key' => [
                4 => 'determined_index_key_4',
            ],
            'string_key'           => [
                'a' => 'string_key_a',
                'b' => 'string_key_b',
            ],
            'string_key_index_key' => [
                'c' => [
                    'string_key_index_key_c_0',
                    'string_key_index_key_c_1',
                ],
            ],
        ];

        self::assertEquals(
            $expected,
            $actual
        );
    }

    public function testCorrectFormatsAsPostmanBulkEdit(): void
    {
        $message = new MultipartMessage($this->test_string);
        $formatter = new PostmanBulkEditFormatter();
        $actual = $formatter->format($message->parse());
        $expected =
            <<<'EXPECTED'
            plain:1
            index_key[0]:index_key_0
            index_key[1]:index_key_1
            determined_index_key[4]:determined_index_key_4
            string_key[a]:string_key_a
            string_key[b]:string_key_b
            string_key_index_key[c][0]:string_key_index_key_c_0
            string_key_index_key[c][1]:string_key_index_key_c_1
            EXPECTED;

        self::assertEquals($expected, $actual);
    }

    public function testCorrectFormatsAsJson(): void
    {
        $message = new MultipartMessage($this->test_string);
        $formatter = new JsonFormatter();
        $actual = $formatter->format($message->parse());
        $expected =
            <<<'EXPECTED'
            {"plain":"1","index_key":["index_key_0","index_key_1"],"determined_index_key":{"4":"determined_index_key_4"},"string_key":{"a":"string_key_a","b":"string_key_b"},"string_key_index_key":{"c":["string_key_index_key_c_0","string_key_index_key_c_1"]}}
            EXPECTED;

        self::assertEquals($expected, $actual);
    }
}
