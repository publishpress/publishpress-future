# Input Bindings UI Prototype

## Overview

This document describes the Input Bindings UI prototype implementation for the PublishPress Future workflow editor. This addresses issue #1505 which required a solution for steps to accept multiple source variables from different triggers.

## Problem Statement

Previously, workflow steps could only select one source variable. When multiple triggers connected to the same step (each providing an equivalent variable like `triggerA.post`, `triggerB.post`), there was no way to tell the step "use whichever one is available." Users were forced to either:

- Duplicate steps per trigger (defeating shared paths)
- Pick one variable and break other paths

## Solution: Input Bindings

The Input Bindings feature allows users to:

1. Define a local binding name (e.g., `thePost`) with a human-readable label (e.g., "The Post")
2. Map multiple source variables to that binding
3. Enforce type consistency across all mapped variables
4. Reference the binding name in step configuration instead of specific trigger variables

## Implementation Details

### Components Created

1. **InputBindingsPanel** (`assets/jsx/workflow-editor/components/input-bindings-panel/index.jsx`)
   - Main panel component that displays in the node inspector
   - Shows list of existing bindings
   - Provides "New binding" button
   - Stores bindings in `node.data.inputBindings`

2. **InputBindingItem** (`assets/jsx/workflow-editor/components/input-bindings-panel/input-binding-item.jsx`)
   - Displays an individual binding with its sources
   - Shows checkboxes for selected sources
   - Provides "Add source" button for compatible variables
   - Allows deletion of bindings

3. **AddSourceModal** (`assets/jsx/workflow-editor/components/input-bindings-panel/add-source-modal.jsx`)
   - Modal dialog for adding a new source to an existing binding
   - Filters available variables by type compatibility
   - Shows radio buttons for source selection

4. **NewBindingModal** (`assets/jsx/workflow-editor/components/input-bindings-panel/new-binding-modal.jsx`)
   - Modal dialog for creating a new binding
   - Allows user to set binding name, label, and data type
   - Validates binding name format (alphanumeric + underscores)
   - Prevents duplicate binding names

### UI Structure

The Input Bindings panel appears in the node inspector for **all workflow steps**, positioned between the Node Inspector Card and the Node Settings Panel.

#### Example UI Flow:

```
┌─ Step: Move post to status ─────────────────┐
│                                             │
│  [Input Bindings]                           │
│  ┌─────────────────────────────────────┐    │
│  │ thePost (Post)                      │    │
│  │   ☑ onLegacyActionTrigger1.post     │    │
│  │   ☑ onPostWorkflowEnable1.post      │    │
│  │   + Add source                      │    │
│  └─────────────────────────────────────┘    │
│  [ + New binding ]                          │
│                                             │
│  Target post: [ thePost ▾ ]                 │
│  New status:  [ Published ▾ ]               │
└─────────────────────────────────────────────┘
```

### Data Structure

Input bindings are stored in the node's data structure:

```javascript
node.data.inputBindings = {
  "thePost": {
    label: "The Post",
    type: "post",
    sources: [
      "onLegacyActionTrigger1.post",
      "onPostWorkflowEnable1.post"
    ]
  },
  "theUser": {
    label: "The User",
    type: "user",
    sources: [
      "onUserLogin1.user"
    ]
  }
}
```

### Key Features

1. **Type Safety**: Only variables with matching data types can be added to a binding
2. **Visual Feedback**: Clear indication of which sources are selected
3. **Validation**: Binding names must be valid identifiers (letters, numbers, underscores)
4. **Empty States**: Helpful messages when no bindings exist or no compatible sources available
5. **Responsive Design**: Works on different screen sizes
6. **Multi-Select Sources**: Use checkboxes to add multiple sources at once in the Add Source modal
7. **Bindings as Outputs**: Bindings automatically become available as variables to downstream steps
8. **Closed by Default**: The Input Bindings panel is collapsed by default to keep the interface clean

## How Bindings Work as Variables

When you create a binding in a step (e.g., Step A with binding `thePost`):

1. The binding is **immediately available** in Step A's own settings fields (e.g., "Target Post" dropdown)
2. The binding also becomes part of Step A's **output schema**
3. Any step **downstream** from Step A can reference `stepA.thePost` in their settings
4. At runtime, the binding resolves to whichever source variable is available
5. This allows you to write configuration once that works with multiple trigger sources

### Example Flow:

```
Trigger 1 (onLegacyAction) → [post output] ──┐
                                              ├──→ Step A [creates binding "thePost", uses it] → Step B [uses stepA.thePost]
Trigger 2 (onWorkflowEnable) → [post output] ┘
```

**In Step A (e.g., "Remove Terms From Post"):**
1. Open Input Bindings panel
2. Click "+ New binding"
3. Create binding: name=`thePost`, label="The Post", type=Post
4. Add sources: `onLegacyAction1.post`, `onWorkflowEnable1.post`
5. Now in Step A's settings, select `thePost` from the "Target Post" dropdown
6. Step A now works with posts from **both** triggers!

**In Step B (downstream):**
- Can also select `stepA.thePost` from any variable dropdown
- Works regardless of which trigger activated the workflow

## Technical Integration

### Files Modified

1. `assets/jsx/workflow-editor/components/node-inspector/index.jsx`
   - Added import for InputBindingsPanel
   - Inserted InputBindingsPanel before NodeSettingsPanel

2. `assets/jsx/workflow-editor/css/index.css`
   - Added CSS import for input-bindings-panel styles

3. `assets/jsx/workflow-editor/utils.jsx`
   - Modified `getNodeOutputSchema()` to include input bindings as output items (for downstream steps)
   - Modified `getNodeVariablesTree()` to include bindings as available variables (for the step's own settings)
   - Bindings are given priority 3 to appear prominently in variable dropdowns

4. `assets/jsx/workflow-editor/components/persistent-panel-body/index.jsx`
   - Added `initialOpen` prop support to control default panel state

### Files Created

1. `assets/jsx/workflow-editor/components/input-bindings-panel/index.jsx` - Main panel component
2. `assets/jsx/workflow-editor/components/input-bindings-panel/input-binding-item.jsx` - Individual binding display
3. `assets/jsx/workflow-editor/components/input-bindings-panel/add-source-modal.jsx` - Multi-select source picker with checkboxes
4. `assets/jsx/workflow-editor/components/input-bindings-panel/new-binding-modal.jsx` - Create new binding form
5. `assets/jsx/workflow-editor/components/input-bindings-panel/style.css` - Component styles

## Building the Assets

To build the JavaScript assets after making changes:

```bash
cd /Users/andersonmartins/Projects/git/publishpress/publishpress-future
npx webpack --mode development
```

For production builds:

```bash
npx webpack --mode production
```

## Next Steps (Backend Integration)

This is a **UI prototype only**. The following backend work is needed for full functionality:

1. **Variable Resolution**: Implement runtime logic to resolve binding references to actual source values
2. **Validation**: Add backend validation for binding configurations
3. **Migration**: Consider how to handle existing workflows
4. **Field Integration**: Update field components (MappedField) to support binding references
5. **Documentation**: Update user-facing documentation with binding usage examples

## Testing the UI

To test the prototype:

1. Build the assets using webpack (see above)
2. Navigate to the WordPress workflow editor
3. Create a workflow with multiple triggers that output similar data types
4. Connect both triggers to a shared step
5. Select the step to view its settings in the inspector
6. You should see the "Input Bindings" panel
7. Click "+ New binding" to create a binding
8. Add sources from the available variables
9. The binding configuration should be saved in the node data

## Notes

- The UI is fully functional for creating and managing bindings
- Bindings are stored in the workflow data structure
- Backend support is needed to actually use these bindings in step execution
- This implementation follows React best practices and WordPress component patterns
- All text is internationalized using the `@publishpress/i18n` package

