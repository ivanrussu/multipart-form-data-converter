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
        Content-Disposition: form-data; name="empty"

        
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
        Content-Disposition: form-data; name="same_key_for_array_and_non_array_1"

        non_array_value
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="same_key_for_array_and_non_array_1[]"

        array_value
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="same_key_for_array_and_non_array_2[]"

        array_value
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="same_key_for_array_and_non_array_2"

        non_array_value
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="array_of_objects[0][props_1]"

        1
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="array_of_objects[0][props_2]"

        2
        -----------------------------359001620640685356211451689597--
         Content-Disposition: form-data; name="array_of_objects[1][props_1]"

        3
        -----------------------------359001620640685356211451689597--
        Content-Disposition: form-data; name="array_of_objects[1][props_2]"

        4
        -----------------------------359001620640685356211451689597--
        MULTIPART;

//    public function testTemp() {
//        $str = '-----------------------------359001620640685356211451689597--
//        Content-Disposition: form-data; name="fruits[0][green]"
//
//        apple
//        -----------------------------359001620640685356211451689597--
//        Content-Disposition: form-data; name="fruits[0][red]"
//
//        tomato
//        -----------------------------359001620640685356211451689597--
//         Content-Disposition: form-data; name="fruits[1][green]"
//
//        kiwi
//        -----------------------------359001620640685356211451689597--
//        Content-Disposition: form-data; name="fruits[1][red]"
//
//        strawberry
//        -----------------------------359001620640685356211451689597--';
//
//
//
//
//        $message = new MultipartMessage($str);
//        $actual = $message->parse();
//        var_dump(json_encode($actual));
//        die;
//    }

    public function testCorrectArrayParsing(): void
    {
        $message = new MultipartMessage($this->test_string);
        $actual = $message->parse();

        $expected = [
            'plain'                => '1',
            'empty'                => '',
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
            'same_key_for_array_and_non_array_1' => [
                'array_value'
            ],
            'same_key_for_array_and_non_array_2' => 'non_array_value',
            'array_of_objects' => [
                [
                    'props_1' => 1,
                    'props_2' => 2,
                ],
                [
                    'props_1' => 3,
                    'props_2' => 4,
                ]
            ]
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
            empty:
            index_key[0]:index_key_0
            index_key[1]:index_key_1
            determined_index_key[4]:determined_index_key_4
            string_key[a]:string_key_a
            string_key[b]:string_key_b
            string_key_index_key[c][0]:string_key_index_key_c_0
            string_key_index_key[c][1]:string_key_index_key_c_1
            same_key_for_array_and_non_array_1[0]:array_value
            same_key_for_array_and_non_array_2:non_array_value
            array_of_objects[0][props_1]:1
            array_of_objects[0][props_2]:2
            array_of_objects[1][props_1]:3
            array_of_objects[1][props_2]:4
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
            {"plain":"1","empty":"","index_key":["index_key_0","index_key_1"],"determined_index_key":{"4":"determined_index_key_4"},"string_key":{"a":"string_key_a","b":"string_key_b"},"string_key_index_key":{"c":["string_key_index_key_c_0","string_key_index_key_c_1"]},"same_key_for_array_and_non_array_1":["array_value"],"same_key_for_array_and_non_array_2":"non_array_value","array_of_objects":[{"props_1":"1","props_2":"2"},{"props_1":"3","props_2":"4"}]}
            EXPECTED;

        self::assertEquals($expected, $actual);
    }
}
