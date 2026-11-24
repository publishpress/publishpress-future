# Node JSON Schema

The node tree is stored as JSON data in the workflow's data. A typical node has the following schema:

```json
{
    "id": "n1718737475263",
    "type": "generic",
    "position": {
        "x": 60,
        "y": 300
    },
    "data": {
        "name": "action/core.stick-post", // Defined in the Node Type class
        "elementaryType": "action", // Defined in the Node Type class
        "version": 1, // Defined in the Node Type class
        "slug": "stickPost1",
        "settings": {
            "post": {
                "variable": "onSavePost1.post"
            }
        }
    },
    "width": 140,
    "height": 65,
    "selected": true,
    "positionAbsolute": {...},
    "dragging": false
}
```

For processing the node the most important property on the schema is `data`.

You can see there is a setting `post`, which is configured to receive the value of the variable `onSavePost1.post`. The node runner will parse that variable and use its value for doing the node's job processing.
