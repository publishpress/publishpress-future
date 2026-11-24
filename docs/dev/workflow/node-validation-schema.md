# Node Validation Schema

The validation schema is evaluated and processed in design time. If the node validator finds any invalid setting or connection, it displays a warning icon in the node and an error message in the settings panel.

There are two groups of validations, both are optional: `connections` and `settings`.

## Validating Node Connections

For the group `connections`, we support the following rules:

- **hasIncomingConnection**: is valid when the node has any incoming connection: `["rule" => "hasIncomingConnection"]`
- **hasOutgoingConnection**: is valid when the node has any outgoing connection: `["rule" => "hasOutgoingConnection"]`
- **hasIncomerOfName**: is valid when the nodes tree is traversed from the current node until the first parent and any of the parent has the name specified by the property "name": `["rule" => "hasIncomerOfName", "name": "action/core.post-save"]`

The schema is:

```php
[
    "connections" => [
        "rules" => [
            [
                "rule" => "<RULE_NAME>",
                "settings" => "",
            ]
        ]
    ],
    ...
]
```

## Validating Node Settings

For the group `settings`, we support the following rules:

- **required**: is valid when the setting is not empty: `["rule" => "required"]`

```php
[
    ...,
    "settings" => [
        "rules" => [
            [
                "rule" => "required",
                "field" => "postQuery.postType",
                "label" => __("Post Type", "publishpress-future-pro"),
            ],
        ],
    ],
]
```

- **datatype**: is valid when the setting is of the specified type:

```php
[
    ...,
    "settings" => [
        "rules" => [
            [
                "rule" => "dataType",
                "field" => "postQuery.postId",
                "type" => "integerList",
                "label" => __("Post ID", "publishpress-future-pro"),
            ],
        ],
    ],
]
```

### Available Data Types Validation

- email: is valid when the setting is a valid email address
- emailList: is valid when the setting is a list of valid email addresses separated by commas
- integer: is valid when the setting is an integer
- integerList: is valid when the setting is a list of integers separated by commas
