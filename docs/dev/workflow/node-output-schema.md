# Node Output Schema

The output schema defines the data that the node will return to the next node in the workflow. The schema is an associative array defined in the Node Type class, returned by the method `getOutputSchema`:

```php
[
    [
        "name" => "posts",
        "type" => "array",
        "itemsType" => "integer",
        "label" => __("Array of queried post IDs", "publishpress-future-pro"),
        "description" => __("The posts found following the criteria of the query.", "publishpress-future-pro"),
    ],
    [
        "name" => "input",
        "type" => "input",
        "label" => __("Step input", "publishpress-future-pro"),
        "description" => __("The input data for this step.", "publishpress-future-pro"),
    ],
]
```
