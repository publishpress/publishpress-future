# Creating a new Step Type

A "Step Type" definition class is used at design time and runtime. It specifies the step's properties such as:

- name
- label
- validation schema
- settings schema
- handle schema
- output schema

## Defining a Step Type

1. Implement the definition class and register it to the `StepTypesModel` class. This class is used to manage the step types in the plugin.
2. Implement the runner class and register it to the `services.php` file in the runner factory service.
3. Implement additional ReactJS components if needed for the settings in the editor.

## Node Elementary Types

There are three elementary types used to separate different types of step.

### Trigger

Type: `trigger`

Steps executed when an event of specific conditions happens to initiate a workflow. An special type of step that do not receive input from other steps, but support outgoing connections to create the flow of steps that should be executed.

### Action

Type: `action`

Steps that execute tasks.

### Advanced Action

Type: `advanced`

Steps that execute advanced tasks like scheduling, decision-making, and others.

Both action and advanced actions are defined similarly, distinguishing only the concept that advanced actions are more specialized steps in the workflow. Some examples of advanced actions are steps that allow to change flow direction depending of certain conditions, steps that schedule tasks for being executed asynchronously, and so on.

## Step Type Interface

Step types must implement the interface `PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface`.

The schemas for settings, validation, output, and handles will be described in more detail in the next sections.

