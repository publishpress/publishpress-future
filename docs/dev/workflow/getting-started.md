# Getting Started with Custom Development

Custom development allows you to extend and tailor the workflow functionality to meet specific needs. By creating new step types and defining custom behaviors, you can enhance the workflow engine to integrate seamlessly with your unique process and applications.

In this section, you will learn how to:

- Create and register new step types
- Define step properties and behaviors

This guide is designed for developers who want to leverage the full potential of the workflow engine, providing detailed instructions and examples to help you get started with custom development.

>:exclamation: Note: If you have feature request or suggestions, please let us know [here](https://github.com/publishpress/PublishPress-Future/discussions). In case you found a bug, please let us know [here](https://github.com/publishpress/PublishPress-Future/issues). Your feedback helps us improve and tailor our tools to better suit your needs.

## Understanding Steps and Nodes

"Step" is a more user-friendly term used in the workflow editor interface. "Node" can be considered synonymous, but is primarily used in the code context, where the workflow is represented as a tree of interconnected nodes. In the code, connections between these nodes are called "edges", another term for "connections".

All steps (triggers, actions, advanced actions) are implemented in the code by specifying a PHP class for defining the "Node Type", a PHP class for the "Node Runner", and registering the new node in the builder and workflow engine.
