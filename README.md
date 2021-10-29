# urlparser
Simple URL parser

This is a simple URL parser, which returns an array of results from url of kind /module/controller/param1:value/param2:value,value

The parser also returns Breadcrumb array, if provided with a names for it (either hard coded or via DB/any other array.

## Usage:

Current url: https://example.com/module/method1/param:1/param2:3,5,6/param3:test

```php
$getUrlDetails = (new SimpleUrlParser)->parseUrlForResults();
```

## Result:
```php
Array
(
    [url] => module/method1/param:1/param2:3,5,6/param3:test
    [bread] => Array
        (
            [Home] => /
            [module] => module/
            [method1] => method1/param:1/
        )

    [module] => module
    [switch] => method1
    [params] => Array
        (
            [param] => 1
            [param2] => Array
                (
                    [0] => 3
                    [1] => 5
                    [2] => 6
                )

            [param3] => test
        )

)
```
Use it if you like :)
