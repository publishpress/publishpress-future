# Registering a Data Type

Data types are used to define the structure and validation rules for data that flows through the workflow editor (Variables).

To create a new data type, follow these steps:

1. Create a new file in `assets/jsx/workflow-editor/components/data-types/` with your data type name, e.g. `my-type.jsx`
2. Export a function that returns the data type definition object. The function should follow this structure:

```jsx
export function MyType() {
    return {
        name: "my-type",
        label: "My Type",
        type: "object",
        objectType: "my-type",
        propertiesSchema: [
            {
                name: "my-property",
                type: "post",
                label: "My Post",
                description: "My post description",
            },
            {
                name: "my-property-2",
                type: "integer",
                label: "My Integer",
                description: "My integer description",
            },
            {
                name: "my-property-3",
                type: "string",
                label: "My String",
                description: "My string description",
            },
            {
                name: "my-property-4",
                type: "user",
                label: "My User",
                description: "My user description",
            },
        ],
    };
}
```

The `type` properties are any of the data types already defined in the `data-types` folder.

3. Import and export the data type in the `index.jsx` file in the `data-types` folder.
4. Add the data type to the `dataTypes` array in the `WorkflowEditorApp` component in the `app.jsx` file.

## Registering custom data type in the workflow engine

To register a custom data type in the workflow engine, you need to create a new class that implements the `VariableResolverInterface` interface.

The class should be located in the `src/Modules/Workflows/Domain/Engine/VariableResolvers` folder and should be named after the data type, e.g. `MyTypeResolver.php`.

Add the new variable resolver to the `$resolversMap` array in the method `expandArguments` in the `src/Modules/Workflows/Domain/Engine/NodeRunnerProcessors/CronStep` class (until we have a variable resolver factory).
