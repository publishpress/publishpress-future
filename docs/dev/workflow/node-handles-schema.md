# Node Handles Schema

The handles schema defines the handles that the node will have. The schema is an associative array defined in the Node Type class, returned by the method `getHandlesSchema`.

```php
[
    "target" => [
        [
            "id" => "input",
            "left" => "50%",
        ]
    ],
    "source" => [
        [
            "id" => "output",
            "left" => "50%",
            "label" => __("Next", "publishpress-future-pro"),
        ]
    ]
]
```
