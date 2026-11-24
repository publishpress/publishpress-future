# Defining a global variable

## Adding it to the store

Edit the function `loadWorkflowSuccess` in the [reducer file](../../src/assets/jsx/workflow-editor/components/workflow-store/reducer.jsx) of the store. Before the `return` statement, add the following code:

```javascript

state = setGlobalVariable(state, {
    payload: {
        name: 'trigger',
        label: 'Trigger',
        type: 'node',
        runtimeOnly: true,
    }
});
```

Node the type of the variable is `node`. This means you need to make sure the type exists as a valid node type in the workflow editor. Check the documentation about [how to define a new data type](define-node-type.md).

## Defining it in the PHP side

Edit the [WorkflowEngine.php](../../src/classes/Modules/Workflows/Domain/Engine/WorkflowEngine.php) file. Add the following code to the `getGlobalVariables` method:

```php
$globals['trigger'] = [
    'id' => 0,
    'name' => '',
    'label' => '',
];
```

The value for this global will be set dynamically when a trigger is fired, but otherwise you should add the default values here.
