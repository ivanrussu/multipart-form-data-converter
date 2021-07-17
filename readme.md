# multipart/form-data converter

Converts multipart requests like 

```
-----------------------------359001620640685356211451689597
Content-Disposition: form-data; name="plain"

1
-----------------------------359001620640685356211451689597
Content-Disposition: form-data; name="index_key[]"

index_key_0
-----------------------------359001620640685356211451689597
```

to JSON 

```json
{
  "plain": 1,
  "index_key": [
    "index_key_0"
  ]
}
```

or Postman's "bulk edit"

```
plain: 1
index_key[0]: index_key_0
```


Supports nested arrays.

## Requirements

PHP 8

## Installation

```
composer install ivanrussu/multipart-form-data-converter 
```

## Usage

```php

use IvanRussu\MultipartFormDataConverter\Formatter\JsonFormatter;
use IvanRussu\MultipartFormDataConverter\Formatter\PostmanBulkEditFormatter;
use IvanRussu\MultipartFormDataConverter\MultipartMessage;


$message = new MultipartMessage($string);
$array = $message->parse();

$formatter = new PostmanBulkEditFormatter();
$postmanBulkEdit = $formatter->format($array);

$formatter = new JsonFormatter();
$json = $formatter->format($array);
```

## Testing

```
composer test ./tests/Tests.php
```