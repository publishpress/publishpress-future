import { applyFilters } from "@wordpress/hooks";
import { select, dispatch } from "@wordpress/data";
import { store as workflowStore } from "./components/workflow-store";
import { store as editorStore } from "./components/editor-store";
import { getIncomers, getOutgoers } from "reactflow";
import { NODE_TYPE_PLACEHOLDER } from "./constants";

const VARIABLE_SOURCE_NODE_INPUT = 'node-input';
const VARIABLE_SOURCE_GLOBAL = 'global';
const DEFAULT_VARIABLE_PRIORITY = 10;

export function addBodyClass(className) {
    if (document.body.classList.contains(className)) return;

    document.body.classList.add(className);
}

export function removeBodyClass(className) {
    if (!document.body.classList.contains(className)) return;

    document.body.classList.remove(className);
}

export function addBodyClasses(classNames) {
    classNames.forEach((className) => addBodyClass(className));
}

export function removeBodyClasses(classNames) {
    classNames.forEach((className) => removeBodyClass(className));
}

/**
 * Returns the block's default menu item classname from its name.
 *
 * @param {string} blockName The block name.
 *
 * @return {string} The block's default menu item class.
 */
export function getNodeMenuDefaultClassName(blockName) {
    // Generated HTML classes for blocks follow the `editor-block-list-item-{name}` nomenclature.
    // Blocks provided by WordPress drop the prefixes 'core/' or 'core-' (historically used in 'core-embed/').
    const className =
        "editor-block-list-item-" +
        blockName.replace(/\//, "-").replace(/^core-/, "");

    return applyFilters(
        "future-pro.getNodeMenuDefaultClassName",
        className,
        blockName,
    );
}

/**
 * Return true if platform is MacOS.
 *
 * @param {Object} _window window object by default; used for DI testing.
 *
 * @return {boolean} True if MacOS; false otherwise.
 */
export function isAppleOS(_window = window) {
    const { platform } = _window.navigator;

    return (
        platform.indexOf("Mac") !== -1 || ["iPad", "iPhone"].includes(platform)
    );
}

export function getNodeIncomers(node) {
    const nodes = select(workflowStore).getNodes();
    const edges = select(workflowStore).getEdges();

    if (!node) return [];

    return getIncomers(node, nodes, edges);
}

export function getNodeIncomersRecursively(node) {
    const incomers = getNodeIncomers(node);

    if (!incomers.length) {
        return [];
    }

    let allIncomers = incomers;

    incomers.forEach((incomer) => {
        const incomerIncomers = getNodeIncomersRecursively(incomer);

        allIncomers = allIncomers.concat(incomerIncomers);
    });

    return allIncomers;
}

export function nodeHasIncomers(node) {
    const incomers = getNodeIncomers(node);
    const nodeHasIncomers = incomers?.length > 0;

    return nodeHasIncomers;
}

export function getNodeOutgoers(node) {
    const nodes = select(workflowStore).getNodes();
    const edges = select(workflowStore).getEdges();

    if (!node) return [];

    return getOutgoers(node, nodes, edges);
}

export function nodeHasOutgoers(node) {
    const outgoers = getNodeOutgoers(node);
    const nodeHasOutgoers = outgoers?.length > 0;

    return nodeHasOutgoers;
}

export function getNodeType(node) {
    return select(editorStore).getNodeTypeByName(node?.data?.name);
}

export function nodeHasInput(node) {
    const incomers = getNodeIncomers(node);
    const nodeHasIncomers = incomers?.length > 0;

    return nodeHasIncomers
        && incomers.filter(
            (incomer) => nodeHasOutput(incomer)
        )?.length > 0;
}

export function nodeHasOutput(node) {
    return getNodeOutputSchema(node).length > 0;
}

export function isDynamicOutputItem(item) {
    if (item.type) {
        return item.type.startsWith('__dynamic__:');
    }

    return false;
}

export function getNodeOutputSchema(node) {
    const nodeType = getNodeType(node);
    let outputSchema = nodeType?.outputSchema || [];
    let dynamicOutputItems = [];

    // Fill the dynamic output item
    if (outputSchema.some(isDynamicOutputItem)) {
        outputSchema = outputSchema.filter((item) => {
            if (isDynamicOutputItem(item)) {
                const settingName = item.type.replace('__dynamic__:', '');

                if (node?.data?.settings?.[settingName]) {
                    node.data.settings[settingName].forEach((subitem) => {
                        const newSubitem = { ...subitem };
                        newSubitem.label = newSubitem.label || newSubitem.name.charAt(0).toUpperCase() + newSubitem.name.slice(1);
                        newSubitem.description = newSubitem.description
                            || item.description
                            || __('Dynamic output item', 'post-expiration');

                        if (newSubitem?.value) {
                            newSubitem.type = newSubitem?.value;
                            delete newSubitem.value;
                        }

                        dynamicOutputItems.push(newSubitem);
                    });
                }

                // Remove the dynamic output item from the output schema
                return false;
            }

            return true;
        });
    }

    if (dynamicOutputItems.length) {
        outputSchema = outputSchema.concat(dynamicOutputItems);
    }

    return outputSchema;
}

export function getGlobalVariablesExpanded(globalVariables) {
    const globalVariablesExpanded = [];

    Object.keys(globalVariables).forEach((variableName) => {
        const variable = globalVariables[variableName];

        globalVariablesExpanded.push({
            name: variableName,
            type: variable.type,
            label: variable.label,
            source: VARIABLE_SOURCE_GLOBAL,
            description: variable.description,
            priority: variable?.priority || DEFAULT_VARIABLE_PRIORITY,
        });
    });

    // Add "global." prefix to all global variables
    globalVariablesExpanded.forEach((variable) => {
        variable.name = 'global.' + variable.name;
        variable.priority = variable?.priority || DEFAULT_VARIABLE_PRIORITY;
    });

    return globalVariablesExpanded;
}

export function mapNodeInputs(node) {
    const previousNodes = getNodeIncomers(node);

    const mappedInput = [];

    previousNodes.forEach((previousNode) => {
        if (! previousNode) {
            return;
        }

        const previousNodeOutputSchema = getNodeOutputSchema(previousNode);

        if (! previousNodeOutputSchema?.length) {
            return;
        }

        const nodeType = getNodeType(previousNode);

        previousNodeOutputSchema.forEach((outputItem) => {
            if (outputItem.type === "input") {
                // Get the previous node outputs to bypass to this node as input
                const previousNodeInputs = mapNodeInputs(previousNode);

                previousNodeInputs.map((inputItem) => {
                    mappedInput.push({
                        ...inputItem,
                        name: `${inputItem.name}`,
                        type: inputItem.type,
                        nodeLabel: nodeType.label,
                        nodeSlug: previousNode.data.slug,
                        priority: inputItem?.priority || DEFAULT_VARIABLE_PRIORITY,
                    });
                });
            } else {
                mappedInput.push({
                    ...outputItem,
                    label: `${outputItem.label}`,
                    name: `${previousNode.data.slug}.${outputItem.name}`,
                    type: outputItem.type,
                    nodeLabel: nodeType.label,
                    nodeSlug: previousNode.data.slug,
                    priority: outputItem?.priority || DEFAULT_VARIABLE_PRIORITY,
                });
            }
        });
    });

    // Remove duplicated inputs
    const uniqueMappedInputs = mappedInput.filter(
        (input, index, self) =>
            index ===
            self.findIndex(
                (t) => t.name === input.name,
            ),  // eslint-disable-line
    );

    // Sort inputs by name
    uniqueMappedInputs.sort((a, b) => {
        if (a.name < b.name) {
            return -1;
        }

        if (a.name > b.name) {
            return 1;
        }

        return 0;
    });

    uniqueMappedInputs.sort((a, b) => a.priority - b.priority);

    return uniqueMappedInputs;
}

export function getStepScopedVariables(node) {
    const nodeType = select(editorStore).getNodeTypeByName(node?.data?.name);

    if (! nodeType?.stepScopedVariablesSchema?.length) {
        return [];
    }

    const stepScopedVariables = nodeType.stepScopedVariablesSchema.map((variable) => {
        return {
            name: node.data.slug + '.' + variable.name,
            label: variable.label,
            children: [],
            type: variable.type,
            itemsType: variable?.itemsType,
            description: variable?.description,
            nodeSlug: node.data.slug,
            priority: variable?.priority || DEFAULT_VARIABLE_PRIORITY,
            context: variable?.context
        };
    });

    return stepScopedVariables;
}

export function getExpandedStepScopedVariables(node) {
    const stepScopedVariables = getStepScopedVariables(node);

    const expandedStepScopedVariables = stepScopedVariables.map(expandVariableWithChildren);

    expandedStepScopedVariables.sort((a, b) => a.priority - b.priority);

    return expandedStepScopedVariables;
}

export function getNodeVariablesTree(node, globalVariables) {
    const mappedNodeInputs = mapNodeInputs(node);
    const globalVariablesToList = getGlobalVariablesExpanded(globalVariables);

    const variablesList = [...mappedNodeInputs, ...globalVariablesToList];

    const expandedVariablesList = variablesList.map(expandVariableWithChildren);

    expandedVariablesList.sort((a, b) => a.priority - b.priority);

    return expandedVariablesList;
}

export function formatVariableStructure(variable) {
    return {
        id: '{{' + variable.name + '}}',
        name: variable.name,
        label: variable.label,
        children: [],
        type: variable.type,
        itemsType: variable?.itemsType,
        description: variable?.description,
        nodeSlug: variable?.nodeSlug,
        priority: variable?.priority || DEFAULT_VARIABLE_PRIORITY,
        context: variable?.context
    };
}

function getVariableProperties(variable) {
    const getDataTypeByName = select(workflowStore).getDataTypeByName;
    const dataType = getDataTypeByName(variable.type);

    if (!dataType?.propertiesSchema?.length) {
        return [];
    }

    const properties = dataType.propertiesSchema.map((property) => {
        const propertyData = formatVariableStructure({
            name: variable.name + '.' + property.name,
            label: property.label,
            type: property?.type,
            itemsType: property?.itemsType,
            description: property?.description,
            nodeSlug: variable?.nodeSlug,
            children: [],
            priority: property?.priority || DEFAULT_VARIABLE_PRIORITY,
            context: property?.context
        });

        if (dataType.primitiveType === 'object') {
            propertyData.children = getVariableProperties(propertyData);
        }

        return propertyData;
    });

    return properties;
}

function expandVariableWithChildren(variable) {
    const getDataTypeByName = select(workflowStore).getDataTypeByName;
    const dataType = getDataTypeByName(variable.type);

    variable = formatVariableStructure(variable);

    if (isDynamicOutputItem(variable)) {
        return variable;
    }

    if (! dataType) {
        console.log('No data type found for variable', variable);
        return variable;
    }

    // If the variable is an object, add its properties as children
    if (dataType.primitiveType === 'object') {
        variable.children = getVariableProperties(variable);
    }

    return variable;
}

export function isValidDataType(dataType, expectedDataTypes) {
    let hasValidDataType = true;

    if (expectedDataTypes?.length) {
        hasValidDataType = expectedDataTypes.includes(dataType?.name);

        if (!hasValidDataType) {
            hasValidDataType = expectedDataTypes.includes(dataType?.primitiveType);
        }

        if (!hasValidDataType && dataType?.primitiveType === 'array') {
            hasValidDataType = expectedDataTypes.includes(dataType?.itemsType);
        }
    }

    return hasValidDataType;
}

export function filterVariablesTreeByDataType(variables, expectedDataTypes) {
    const getDataTypeByName = select(workflowStore).getDataTypeByName;
    let filteredVariables = [];

    variables.forEach((variable) => {
        const dataType = getDataTypeByName(variable.type);
        const itemsType = variable?.itemsType;
        let variableHasValidDataType = isValidDataType(dataType, expectedDataTypes);

        if (variable.type === 'array' && itemsType && !variableHasValidDataType) {
            const expectedItemsDataTypes = expectedDataTypes.map((type) => {
                if (type.startsWith('array:')) {
                    return type.replace('array:', '');
                }

                return null;
            });

            const itemsDataType = getDataTypeByName(itemsType);
            variableHasValidDataType = isValidDataType(itemsDataType, expectedItemsDataTypes);
        }

        let validVariable = null;
        let validChildren = [];

        // Desn't include the variable properties if the variable itself is invalid
        if (dataType.primitiveType === 'object') {
            const properties = dataType.propertiesSchema;

            // Ignore the properties if the variable itself is valid.
            properties.forEach((property) => {
                const propertyDataType = getDataTypeByName(property.type);
                const propertyHasValidDataType = isValidDataType(propertyDataType, expectedDataTypes);

                if (propertyHasValidDataType) {
                    const filteredVariable = {
                        id: variable.name + '.' + property.name,
                        name: variable.name + '.' + property.name,
                        label: variable.label + ' -> ' + property.label,
                    };

                    validChildren.push(formatVariableStructure(filteredVariable));
                }
            });

            validVariable = {
                id: variable.id,
                name: variable.label,
                children: validChildren,
                type: variable.type,
            };

            if (variableHasValidDataType) {
                filteredVariables.push(validVariable);
            } else if (validChildren.length) {
                filteredVariables = filteredVariables.concat(validChildren);
            }
        } else {
            if (variableHasValidDataType) {
                filteredVariables.push({
                    id: variable.id,
                    name: variable.label,
                    children: [],
                    type: variable.type,
                });
            }
        }
    });

    return filteredVariables;
}

export const getId = (prefix = "node") => {
    // We are subtracting the current date from the date 2024-01-01,
    // and using a 32 base number to get a smaller number
    const currentTimestamp = new Date().getTime();
    const pastTimestamp = new Date('2024-01-01 00:00:00').getTime();

    const UID = (currentTimestamp - pastTimestamp).toString(32);

    return `${prefix}_${UID}`
};

export function incrementAndGetNodeSlug(nodeItem) {
    const nodeType = select(editorStore).getNodeTypeByName(nodeItem.name);
    const baseSlugCounts = select(workflowStore).getBaseSlugCounts();

    let baseSlug = nodeType.baseSlug;

    if (!baseSlug) {
        baseSlug = "node";
    }

    dispatch(workflowStore).incrementBaseSlugCounts(baseSlug);

    const count = baseSlugCounts[baseSlug] || 0;

    return `${baseSlug}${count + 1}`;
};

export function createNewNode({item, position, reactFlowInstance}) {
    const slug = incrementAndGetNodeSlug(item);
    let nodes = select(workflowStore).getNodes();

    const idPrefix = item.baseSlug ?? "node";

    const newNode = {
        id: getId(idPrefix),
        type: item.type,
        position: position,
        data: {
            name: item.name,
            elementaryType: item.elementaryType,
            version: item.version,
            slug: slug,
            settings: {},
        },
    };

    // Add default settings values if specified in the node type settings schema.
    if (item.settingsSchema) {
        item.settingsSchema.forEach((panel) => {
            panel.fields.forEach((field) => {
                if (field.default === undefined) {
                    return;
                }

                newNode.data.settings[field.name] = field.default;
            });
        });
    }

    nodes = nodes.filter((node) => node.data.elementaryType !== NODE_TYPE_PLACEHOLDER);

    dispatch(workflowStore).setNodes(nodes.concat(newNode));

    updateFlowInEditedWorkflow(reactFlowInstance);

    return newNode;
}

export function updateFlowInEditedWorkflow(reactFlowInstance) {
    // We need to delay the update of the flow to avoid missing the changes.
    setTimeout(() => {
        dispatch(workflowStore).setEditedWorkflowAttribute("flow", reactFlowInstance.toObject());
    }, 400);
}

export const newTriggerPlaceholderNode = () => {
    return {
        id: getId("triggerPlaceholder"),
        type: 'triggerPlaceholder',
        position: { x: 0, y: 0 },
        data: {
            name: 'placeholder/trigger',
            elementaryType: NODE_TYPE_PLACEHOLDER,
            version: 1,
            slug: 'triggerPlaceholder1',
        },
    };
}

export function getNodeById(id, nodes = null) {
    if (! nodes) {
        nodes = select(workflowStore).getNodes();
    }

    return nodes.find((node) => node.id === id);
}

export function getVariableDataTypeByVariableName(targetVariableName, variables) {
    const plainTargetVariableName = targetVariableName.replace('{{', '').replace('}}', '');

    // Problem: the post thype, for example is a string. Should we have a sub type, or specialized type? Autocomplete type?
    let foundDataType = null;

    variables.forEach(variable => {
        // Check if the variable.name is the same as the targetVariableName
        if (variable.name === plainTargetVariableName) {
            foundDataType = variable.type;
        }

        // Check if the variable.name is the beginning of the targetVariableName
        if (plainTargetVariableName.startsWith(variable.name)) {
            if (variable.children.length > 0) {
                foundDataType = getVariableDataTypeByVariableName(targetVariableName, variable.children);
            }
        }
    });

    return foundDataType;
}

export function getVariableLabelByVariableName(targetVariableName, variables) {
    const plainTargetVariableName = targetVariableName.replace('{{', '').replace('}}', '');

    let label = null;

    for (const variable of variables) {
        if (! plainTargetVariableName.toLowerCase().startsWith(variable.name.toLowerCase())) {
            continue;
        }

        if (variable.name.toLowerCase() === plainTargetVariableName.toLowerCase()) {
            label = variable.label;
            break;
        }

        if (plainTargetVariableName.toLowerCase().startsWith(variable.name.toLowerCase()) && variable.children.length > 0) {
            label = variable.label + ' -> ' + getVariableLabelByVariableName(targetVariableName, variable.children);

            if (label) {
                break;
            }
        }
    }

    if (! label) {
        return null;
    }

    return label;
}

export function getNodeHandleSchema(nodeType, data, type) {
    if (!nodeType?.handleSchema?.[type]) {
        return [];
    }

    return nodeType.handleSchema[type].reduce((handleList, handle) => {
        if (!handle?.type) {
            return [...handleList, handle];
        }

        if (!handle.type.startsWith('__dynamic__:')) {
            return handleList;
        }

        const settingName = handle.type.replace('__dynamic__:', '');
        const options = data?.settings?.[settingName];

        return [
            ...handleList,
            ...options.map(option => ({
                id: option.name,
                label: option.label,
            }))
        ];
    }, []);
}

// Todo: Should we move all similar functions to the workflow store?
export function getNodeSourceHandleNamesByNodeId(nodeId) {
    const nodes = select(workflowStore).getNodes();

    const node = nodes.find((node) => node.id === nodeId);
    const nodeType = select(editorStore).getNodeTypeByName(node.data.name);
    const sourceHandles = getNodeHandleSchema(nodeType, node.data, 'source');

    return sourceHandles.map((handle) => handle.id);
}
