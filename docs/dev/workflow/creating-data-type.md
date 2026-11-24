# Defining a data type

## Defining the data type schema

Create a new file in the [data-types](../../../src/assets/jsx/workflow-editor/components/data-types) folder. The file should export a function that returns an object with the following properties:

```javascript
export function NodeData() {
    return {
        name: "node",
        label: "Workflow Node",
        type: "object",
        propertiesSchema: [
            {
                name: "id",
                type: "integer",
                label: "ID",
            },
            {
                name: "name",
                type: "string",
                label: "Name",
            },
            {
                name: "label",
                type: "string",
                label: "Label",
            }
        ],
    };
}

export default NodeData;
```

The `propertiesSchema` property is an array of objects that define the properties of the data type. Only data that are "object" require a `propertiesSchema` property. If it is a simple scalar type, you can omit this property.

Edit the [index.jsx](../../../src/assets/jsx/workflow-editor/components/data-types/index.jsx) file exporting the new data type:

```javascript
export { default as NodeData } from './node';
```

## Adding it to the store

Edit the constant `dataTypes` in the [data.jsx](../../../src/assets/jsx/workflow-editor/components/data.jsx) file. Instantiate the function that defines the data type as an item of the array. For example:

```javascript
const dataTypes = [
    PostData(),
    BooleanData(),
    DatetimeData(),
    IntegerData(),
    StringData(),
    EmailData(),
    InputData(),
    WorkflowDataType(),
    UserData(),
    SiteData(),
    NodeData(),
];
