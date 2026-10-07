import { NODE_TYPE_PLACEHOLDER } from '../../constants';

const EXPRESSION_FIELD_TYPES = new Set([
    'expression',
    'debugData',
    'askForConfirmation',
]);

const NATURAL_LANGUAGE_FIELD_TYPES = new Set([
    'conditional',
    'postFilter',
    'postSearchQuery',
    'conditionalDateOffset',
]);

/**
 * Compare two values for equality (shallow for primitives, JSON for objects).
 *
 * @param {*} a
 * @param {*} b
 * @return {boolean}
 */
function valuesEqual(a, b) {
    if (a === b) {
        return true;
    }

    if (a === undefined || a === null) {
        return b === undefined || b === null || b === '';
    }

    if (b === undefined || b === null) {
        return a === '' || a === null || a === undefined;
    }

    if (typeof a === 'object' || typeof b === 'object') {
        try {
            return JSON.stringify(a) === JSON.stringify(b);
        } catch (error) {
            return false;
        }
    }

    return String(a) === String(b);
}

/**
 * Format an action argument entry for display.
 *
 * @param {Object} argument
 * @return {string}
 */
function formatActionArgEntry(argument) {
    const argumentName = argument?.name || '(unnamed)';
    const dataType = argument?.type || argument?.value || 'integer';
    const expression = argument?.expression?.expression;

    if (expression) {
        return `${argumentName}: ${expression} (${dataType})`;
    }

    return `${argumentName}: (${dataType})`;
}

/**
 * Format an object setting value using field-aware rules.
 *
 * @param {Object} value
 * @param {Object|null} field
 * @return {string}
 */
function formatObjectSettingValue(value, field) {
    const fieldType = field?.type;

    if (NATURAL_LANGUAGE_FIELD_TYPES.has(fieldType) || (typeof value.natural === 'string' && 'json' in value)) {
        return value.natural || '';
    }

    if (EXPRESSION_FIELD_TYPES.has(fieldType) || isExpressionObject(value)) {
        return value.expression || '';
    }

    return JSON.stringify(value);
}

/**
 * Check whether a value is an expression object.
 *
 * @param {*} value
 * @return {boolean}
 */
function isExpressionObject(value) {
    return Boolean(
        value
        && typeof value === 'object'
        && !Array.isArray(value)
        && typeof value.expression === 'string'
        && !('json' in value)
    );
}

/**
 * Format a setting value for display in the summary.
 *
 * @param {*} value
 * @param {Object|null} field
 * @return {string}
 */
function formatSettingValue(value, field = null) {
    if (value === undefined || value === null) {
        return '';
    }

    if (typeof value === 'boolean') {
        return value ? 'true' : 'false';
    }

    if (Array.isArray(value)) {
        if (field?.type === 'actionArgs') {
            return value.map(formatActionArgEntry).join('\n   ');
        }

        return value.map((item) => formatSettingValue(item, field)).join(', ');
    }

    if (typeof value === 'object') {
        return formatObjectSettingValue(value, field);
    }

    return String(value);
}

/**
 * Get the schema default value for a field.
 *
 * @param {Object} field
 * @return {*}
 */
function getFieldDefault(field) {
    return field.default;
}

/**
 * Find a field definition in the settings schema by name.
 *
 * @param {Array} settingsSchema
 * @param {string} fieldName
 * @return {Object|null}
 */
function findFieldInSchema(settingsSchema, fieldName) {
    if (!settingsSchema || !Array.isArray(settingsSchema)) {
        return null;
    }

    for (const panel of settingsSchema) {
        if (!panel?.fields) {
            continue;
        }

        const field = panel.fields.find((item) => item.name === fieldName);

        if (field) {
            return field;
        }
    }

    return null;
}

/**
 * Get non-default settings for a node compared to its type schema.
 *
 * @param {Object} node
 * @param {Object} nodeType
 * @return {Array<{label: string, value: string}>}
 */
export function getNonDefaultSettings(node, nodeType) {
    const settings = node?.data?.settings || {};
    const settingsSchema = nodeType?.settingsSchema || [];
    const nonDefaults = [];
    const processedFields = new Set();

    if (Array.isArray(settingsSchema)) {
        settingsSchema.forEach((panel) => {
            if (!panel?.fields) {
                return;
            }

            panel.fields.forEach((field) => {
                processedFields.add(field.name);

                const storedValue = settings[field.name];
                const defaultValue = getFieldDefault(field);

                if (defaultValue === undefined) {
                    if (storedValue === undefined || storedValue === null || storedValue === '') {
                        return;
                    }
                } else if (valuesEqual(storedValue, defaultValue)) {
                    return;
                }

                nonDefaults.push({
                    label: field.label || field.name,
                    value: formatSettingValue(storedValue, field),
                });
            });
        });
    }

    Object.keys(settings).forEach((fieldName) => {
        if (processedFields.has(fieldName)) {
            return;
        }

        const storedValue = settings[fieldName];

        if (storedValue === undefined || storedValue === null || storedValue === '') {
            return;
        }

        const field = findFieldInSchema(settingsSchema, fieldName);

        nonDefaults.push({
            label: field?.label || fieldName,
            value: formatSettingValue(storedValue, field),
        });
    });

    return nonDefaults;
}

/**
 * Get a display label for a node in diagrams and step lists.
 *
 * @param {Object} node
 * @param {Object} nodeType
 * @return {string}
 */
export function getNodeDisplayLabel(node, nodeType) {
    const typeLabel = nodeType?.label || node?.data?.name || 'Node';
    const customLabel = node?.data?.label;

    if (customLabel && customLabel !== typeLabel) {
        return `${typeLabel} (${customLabel})`;
    }

    return typeLabel;
}

/**
 * Get a prefixed diagram label (Trigger:, Action:, etc.).
 *
 * @param {Object} node
 * @param {Object} nodeType
 * @return {string}
 */
function getNodeDiagramLabel(node, nodeType) {
    const displayLabel = getNodeDisplayLabel(node, nodeType);
    const elementaryType = node?.data?.elementaryType || nodeType?.elementaryType;

    switch (elementaryType) {
        case 'trigger':
            return `Trigger: ${displayLabel}`;
        case 'action':
            return `Action: ${displayLabel}`;
        case 'advanced':
            return `Advanced: ${displayLabel}`;
        default:
            return displayLabel;
    }
}

/**
 * Filter out placeholder nodes from the workflow.
 *
 * @param {Array} nodes
 * @return {Array}
 */
function getWorkflowNodes(nodes) {
    return (nodes || []).filter(
        (node) => node?.data?.elementaryType !== NODE_TYPE_PLACEHOLDER
            && node?.type !== 'triggerPlaceholder'
            && node?.type !== 'nodePlaceholder'
    );
}

/**
 * Find trigger nodes in the workflow.
 *
 * @param {Array} nodes
 * @param {Array} edges
 * @return {Array}
 */
function getTriggerNodes(nodes, edges) {
    const workflowNodes = getWorkflowNodes(nodes);

    const triggers = workflowNodes.filter(
        (node) => node?.data?.elementaryType === 'trigger' || node?.type === 'trigger'
    );

    if (triggers.length > 0) {
        return triggers;
    }

    const nodesWithNoIncoming = workflowNodes.filter((node) => {
        return !(edges || []).some((edge) => edge.target === node.id);
    });

    if (nodesWithNoIncoming.length > 0) {
        return nodesWithNoIncoming;
    }

    return workflowNodes.slice(0, 1);
}

/**
 * Build an ordered list of node IDs starting from triggers via BFS.
 *
 * @param {Array} nodes
 * @param {Array} edges
 * @return {Array<string>}
 */
export function buildFlowOrder(nodes, edges) {
    const workflowNodes = getWorkflowNodes(nodes);
    const nodeMap = new Map(workflowNodes.map((node) => [node.id, node]));
    const triggers = getTriggerNodes(workflowNodes, edges);
    const ordered = [];
    const visited = new Set();
    const queue = [...triggers.map((node) => node.id)];

    while (queue.length > 0) {
        const nodeId = queue.shift();

        if (visited.has(nodeId) || !nodeMap.has(nodeId)) {
            continue;
        }

        visited.add(nodeId);
        ordered.push(nodeId);

        const outgoingEdges = (edges || [])
            .filter((edge) => edge.source === nodeId)
            .sort((a, b) => {
                const handleA = a.sourceHandle || '';
                const handleB = b.sourceHandle || '';

                return handleA.localeCompare(handleB);
            });

        outgoingEdges.forEach((edge) => {
            if (!visited.has(edge.target)) {
                queue.push(edge.target);
            }
        });
    }

    workflowNodes.forEach((node) => {
        if (!visited.has(node.id)) {
            ordered.push(node.id);
        }
    });

    return ordered;
}

/**
 * Get the human-readable label for an edge source handle.
 *
 * @param {Object} edge
 * @param {Object} sourceNode
 * @param {Object} nodeType
 * @return {string}
 */
function getHandleLabel(edge, sourceNode, nodeType) {
    const handleId = edge.sourceHandle;

    if (!handleId) {
        return 'Next';
    }

    const handleSchema = nodeType?.handleSchema?.source || [];

    const staticHandle = handleSchema.find((handle) => handle.id === handleId);

    if (staticHandle?.label) {
        return staticHandle.label;
    }

    const dynamicHandles = [];

    handleSchema.forEach((handle) => {
        if (handle?.type?.startsWith('__dynamic__:')) {
            const settingName = handle.type.replace('__dynamic__:', '');
            const options = sourceNode?.data?.settings?.[settingName] || [];

            options.forEach((option) => {
                dynamicHandles.push(option);
            });
        }
    });

    const dynamicHandle = dynamicHandles.find((handle) => handle.name === handleId || handle.id === handleId);

    if (dynamicHandle?.label) {
        return dynamicHandle.label;
    }

    return handleId;
}

/**
 * Draw a single ASCII box for a node label.
 *
 * @param {string} label
 * @return {Array<string>}
 */
function drawBox(label) {
    const innerWidth = Math.max(label.length, 19);
    const horizontalRule = '─'.repeat(innerWidth + 2);
    const paddedLabel = ` ${label.padEnd(innerWidth)} `;

    return [
        `┌${horizontalRule}┐`,
        `│${paddedLabel}│`,
        `└${horizontalRule}┘`,
    ];
}

/**
 * Draw vertical connector lines between boxes.
 *
 * @param {number} indent
 * @return {Array<string>}
 */
function drawConnector(indent = 0) {
    const prefix = ' '.repeat(indent);

    return [
        `${prefix}     │`,
        `${prefix}     v`,
    ];
}

/**
 * Recursively generate ASCII diagram lines for a node subtree.
 *
 * @param {string} nodeId
 * @param {Map} nodeMap
 * @param {Array} edges
 * @param {Function} getNodeTypeByName
 * @param {Set} visitedInPath
 * @param {Set} globalVisited
 * @param {number} indent
 * @return {Array<string>}
 */
function generateNodeDiagramLines(nodeId, nodeMap, edges, getNodeTypeByName, visitedInPath, globalVisited, indent = 0) {
    const node = nodeMap.get(nodeId);

    if (!node) {
        return [];
    }

    globalVisited.add(nodeId);

    const prefix = ' '.repeat(indent);
    const nodeType = getNodeTypeByName(node.data.name) || {};
    const label = getNodeDiagramLabel(node, nodeType);
    const lines = drawBox(label).map((line) => prefix + line);

    if (visitedInPath.has(nodeId)) {
        lines.push(`${prefix}     (cycle detected)`);
        return lines;
    }

    const newVisited = new Set(visitedInPath);
    newVisited.add(nodeId);

    const outgoingEdges = (edges || [])
        .filter((edge) => edge.source === nodeId)
        .sort((a, b) => {
            const handleA = a.sourceHandle || '';
            const handleB = b.sourceHandle || '';

            return handleA.localeCompare(handleB);
        });

    if (outgoingEdges.length === 0) {
        return lines;
    }

    if (outgoingEdges.length === 1) {
        lines.push(...drawConnector(indent));

        const childLines = generateNodeDiagramLines(
            outgoingEdges[0].target,
            nodeMap,
            edges,
            getNodeTypeByName,
            newVisited,
            globalVisited,
            indent
        );

        lines.push(...childLines);

        return lines;
    }

    lines.push(`${prefix}     │`);

    outgoingEdges.forEach((edge, index) => {
        const isLast = index === outgoingEdges.length - 1;
        const branchPrefix = isLast ? '└─' : '├─';
        const handleLabel = getHandleLabel(edge, node, nodeType);
        const targetNode = nodeMap.get(edge.target);
        const targetType = targetNode ? getNodeTypeByName(targetNode.data.name) || {} : {};
        const targetLabel = targetNode
            ? getNodeDiagramLabel(targetNode, targetType)
            : edge.target;

        lines.push(`${prefix}     ${branchPrefix}[${handleLabel}]──> ${targetLabel}`);

        const childLines = generateNodeDiagramLines(
            edge.target,
            nodeMap,
            edges,
            getNodeTypeByName,
            newVisited,
            globalVisited,
            indent + 5
        );

        if (childLines.length > 0) {
            lines.push(...childLines);
        }
    });

    return lines;
}

/**
 * Generate an ASCII diagram representing the workflow flow.
 *
 * @param {Array} nodes
 * @param {Array} edges
 * @param {Function} getNodeTypeByName
 * @return {string}
 */
export function generateAsciiDiagram(nodes, edges, getNodeTypeByName) {
    const workflowNodes = getWorkflowNodes(nodes);

    if (workflowNodes.length === 0) {
        return '(No workflow steps configured)';
    }

    const triggers = getTriggerNodes(workflowNodes, edges);
    const nodeMap = new Map(workflowNodes.map((node) => [node.id, node]));
    const lines = [];
    const globalVisited = new Set();

    triggers.forEach((trigger, index) => {
        if (index > 0) {
            lines.push('');
        }

        const diagramLines = generateNodeDiagramLines(
            trigger.id,
            nodeMap,
            edges,
            getNodeTypeByName,
            new Set(),
            globalVisited,
            0
        );

        lines.push(...diagramLines);
    });

    workflowNodes.forEach((node) => {
        if (globalVisited.has(node.id)) {
            return;
        }

        lines.push('');
        lines.push('(Disconnected step)');

        const diagramLines = generateNodeDiagramLines(
            node.id,
            nodeMap,
            edges,
            getNodeTypeByName,
            new Set(),
            globalVisited,
            0
        );

        lines.push(...diagramLines);
    });

    return lines.join('\n');
}

/**
 * Generate the steps and configuration section.
 *
 * @param {Array} nodes
 * @param {Array} edges
 * @param {Function} getNodeTypeByName
 * @return {string}
 */
function generateStepsSection(nodes, edges, getNodeTypeByName) {
    const orderedIds = buildFlowOrder(nodes, edges);
    const nodeMap = new Map(getWorkflowNodes(nodes).map((node) => [node.id, node]));
    const lines = [];

    if (orderedIds.length === 0) {
        return '(No steps configured)';
    }

    orderedIds.forEach((nodeId, index) => {
        const node = nodeMap.get(nodeId);

        if (!node) {
            return;
        }

        const nodeType = getNodeTypeByName(node.data.name) || {};
        const displayLabel = getNodeDisplayLabel(node, nodeType);
        const slug = node.data.slug || node.id;
        const nodeName = node.data.name || 'unknown';

        lines.push(`${index + 1}. ${displayLabel} (${slug}) [${nodeName}]`);

        const nonDefaultSettings = getNonDefaultSettings(node, nodeType);

        if (nonDefaultSettings.length === 0) {
            lines.push('   (default settings)');
        } else {
            nonDefaultSettings.forEach((setting) => {
                lines.push(`   - ${setting.label}: ${setting.value}`);
            });
        }
    });

    return lines.join('\n');
}

/**
 * Generate a plain-text summary for a single workflow step.
 *
 * @param {Object} params
 * @param {Object} params.node
 * @param {Object} params.nodeType
 * @return {string}
 */
export function generateNodeSummaryText({ node, nodeType }) {
    if (!node) {
        return '';
    }

    const resolvedNodeType = nodeType || {};
    const displayLabel = getNodeDisplayLabel(node, resolvedNodeType);
    const slug = node.data?.slug || node.id;
    const nodeName = node.data?.name || 'unknown';
    const elementaryType = node?.data?.elementaryType || resolvedNodeType?.elementaryType;

    const lines = [
        `${displayLabel} (${slug}) [${nodeName}]`,
    ];

    if (elementaryType) {
        lines.push(`   Type: ${elementaryType}`);
    }

    const nonDefaultSettings = getNonDefaultSettings(node, resolvedNodeType);

    if (nonDefaultSettings.length === 0) {
        lines.push('   (default settings)');
    } else {
        nonDefaultSettings.forEach((setting) => {
            lines.push(`   - ${setting.label}: ${setting.value}`);
        });
    }

    return lines.join('\n');
}

/**
 * Generate the full plain-text workflow summary for sharing.
 *
 * @param {Object} params
 * @param {Object} params.workflow
 * @param {Function} params.getNodeTypeByName
 * @return {string}
 */
export function generateWorkflowSummaryText({ workflow, getNodeTypeByName }) {
    const title = workflow?.title || '(Untitled workflow)';
    const description = workflow?.description || '';
    const status = workflow?.status || 'draft';
    const nodes = workflow?.flow?.nodes || [];
    const edges = workflow?.flow?.edges || [];
    const workflowNodes = getWorkflowNodes(nodes);

    const lines = [
        `Workflow: ${title}`,
    ];

    if (description.trim()) {
        lines.push(`Description: ${description.trim()}`);
    }

    lines.push(`Status: ${status}`);
    lines.push('');
    lines.push('Flow:');

    if (workflowNodes.length === 0) {
        lines.push('(Empty workflow — add a trigger to get started)');
    } else {
        lines.push(generateAsciiDiagram(nodes, edges, getNodeTypeByName));
    }

    lines.push('');
    lines.push('Steps & Configuration:');
    lines.push(generateStepsSection(nodes, edges, getNodeTypeByName));

    return lines.join('\n');
}
