# Node Settings Schema

The settings schema defines the fields displayed in the node inspector when the node is selected in the builder to be configured.

The schema is an associative array defined in the Node Type class, returned by the method `getSettingsSchema`. Each item in the first level of the array is a group of fields. The group of fields has a list of fields following structure:

```php
[
  "label" => "The settings group label",
  "description" => "The settings group description",
  "fields" => [],
]
```

The property `fields` must have one or more associative arrays with the following structure:

```php
[
  "name" => "fieldName",
  "type" => "postInput", // See the list of available field types below.
  "label" => "Post",
  "description" => "The post that will receive the metadata",
]
```
