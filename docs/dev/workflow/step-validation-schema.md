# Step validation schema

## Requiring specific ascendent connection

Add the following rules to the `getValidationSchema` method in the step definition class.

```php
public function getValidationSchema(): array
{
    return [
        "connections" => [
            "rules" => [
                [
                    "rule" => "hasIncomingConnection",
                ],
                [
                    "rule" => "hasIncomerOfName",
                    "name" => "advanced/core.schedule",
                    "message" => __(
                        "Please include a \"Schedule\" step earlier in this branch of the workflow.",
                        "post-expirator"
                    ),
                ],
            ],
        ],
    ];
}
```
