import { store as workflowStore } from "../workflow-store";
import { store as editorStore } from "../editor-store";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect, useMemo, useCallback } from "@wordpress/element";
import { __, sprintf } from "@wordpress/i18n";
import { nodeHasIncomers, nodeHasOutgoers, getNodeIncomersRecursively } from "../../utils";
import isEmail from "validator/lib/isEmail";
import isInt from "validator/lib/isInt";
import { useDebounce } from "@wordpress/compose";
import { getNodeVariablesTree, filterVariablesTreeByDataType } from "../../utils";

function isVariable(value) {
    const trimmedValue = value.trim();
    return trimmedValue.startsWith('{{') && trimmedValue.endsWith('}}');
}

const DEBOUNCE_TIME = 250;

export function NodeValidator({})
{
    const {
        nodes,
        edges,
        getNodeTypeByName,
        globalVariables,
    } = useSelect((select) => {
        return {
            nodes: select(workflowStore).getNodes(),
            edges: select(workflowStore).getEdges(),
            getNodeTypeByName: select(editorStore).getNodeTypeByName,
            globalVariables: select(workflowStore).getGlobalVariables(),
        }
    });

    const {
        addNodeError,
        resetNodeErrors,
    } = useDispatch(workflowStore);

    const nodeSlugs = useMemo(() => {
        return nodes.map((node) => {
            return node.data.slug;
        });
    }, [nodes]);

    const isVariableValid = useCallback((node, variable, ruleData) => {
        const successfulResult = {
            isValid: true,
            error: null,
        };

        if (variable?.rule && variable?.dataType) {
            return successfulResult;
        }

        if (typeof variable !== 'object') {
            variable = [variable];
        }


        if (ruleData?.skipIfEmpty) {
            if (variable === '') {
                return successfulResult;
            }

            if (Array.isArray(variable) && variable.length === 0) {
                return successfulResult;
            }

            if (typeof variable === 'object' && Object.keys(variable).length === 0) {
                return successfulResult;
            }
        }

        let contextVariables = getNodeVariablesTree(node, globalVariables);

        if (ruleData?.dataType) {
            if (! Array.isArray(ruleData.dataType)) {
                ruleData.dataType = [ruleData.dataType];
            }
            contextVariables = filterVariablesTreeByDataType(contextVariables, ruleData.dataType);
        }

        let onlyValidValues = true;
        let invalidVariable = '';

        for (let i = 0; i < variable.length; i++) {
            const variableItem = variable[i].trim();

            if (! variableItem.startsWith('{{')) {
                continue;
            }

            if (! variableItem.endsWith('}}')) {
                invalidVariable = variableItem;
                onlyValidValues = false;
                break;
            }

            let variableIsFound = false;

            contextVariables.forEach((contextVariable) => {
                if (contextVariable.id === variableItem) {
                    variableIsFound = true;
                }
            });

            if (! variableIsFound) {
                onlyValidValues = false;
                invalidVariable = variableItem;
                break;
            }
        }

        if (onlyValidValues) {
            return successfulResult;
        }

        return {
            isValid: false,
            error: sprintf(
                __('The field "%s" contains an invalid variable.', 'post-expirator'),
                ruleData?.fieldLabel
            ),
            details: sprintf(
                __('The variable "%s" is not available in the current context. Please, check if it is spelled correctly.', 'post-expirator'),
                invalidVariable
            ),
        };
    }, [globalVariables, getNodeVariablesTree, filterVariablesTreeByDataType, getNodeTypeByName]);

    // We are doing a simple validation here. Maybe we should do a more complex one using a parser in the future.
    const isExpressionValid = useCallback((expression, ruleData) => {
        let invalidExpression = false;
        let detailsMessage = '';

        const successfulResult = {
            isValid: true,
            error: null,
        };


        if (! expression?.includes('{{')) {
            return successfulResult;
        }

        if (expression === '{{input}}') {
            return successfulResult;
        }

        const expressions = expression.match(/{{[^}]+}}/g);

        if (expressions) {
            expressions.forEach((expression) => {
                expression = expression.replace('{{', '').replace('}}', '');
                expression = expression.trim();

                // Remove the optional helper parts, the first and all after the second parts.
                expression = expression.split(' ');
                if (expression.length > 1) {
                    expression = expression[1];
                } else {
                    expression = expression[0];
                }

                const expressionParts = expression.split('.');
                const slug = expressionParts[0];

                if (slug === 'global') {
                    return successfulResult;
                }

                if (ruleData?.allowedVariables?.includes(slug)) {
                    return successfulResult;
                }

                if (! nodeSlugs.includes(slug)) {
                    invalidExpression = true;
                    detailsMessage = sprintf(
                        // translators: %s is the workflow step slug.
                        __('"%s" is not a variable or step slug.', 'post-expirator'),
                        slug
                    );
                }
            });
        }

        if (expression.includes('{{}}')) {
            invalidExpression = true;
            detailsMessage = __('Empty placeholder is not allowed.', 'post-expirator');
        }

        const countOpenPlaceholders = (expression) => {
            return (expression.match(/{{/g) || []).length;
        }

        const countClosePlaceholders = (expression) => {
            return (expression.match(/}}/g) || []).length;
        }

        if (countOpenPlaceholders(expression) > countClosePlaceholders(expression)) {
            invalidExpression = true;
            detailsMessage = __('Unclosed placeholder are not allowed.', 'post-expirator');
        }

        if (countOpenPlaceholders(expression) < countClosePlaceholders(expression)) {
            invalidExpression = true;
            detailsMessage = __('Unopened placeholder are not allowed.', 'post-expirator');
        }

        if (invalidExpression) {
            return {
                isValid: false,
                error: sprintf(
                    // translators: %s is the field label.
                    __('Invalid expression on %s', 'post-expirator'),
                    ruleData?.fieldLabel
                ),
                details: detailsMessage,
            }
        }

        return successfulResult;
    }, [nodeSlugs]);

    const isOptionsValid = useCallback((options, ruleData) => {
        if (! Array.isArray(options)) {
            return {
                isValid: false,
                error: sprintf(
                    // translators: %s is the field label.
                    __('The field %s must be a list of options.', 'post-expirator'),
                    ruleData?.fieldLabel
                ),
            };
        }

        const successfulResult = {
            isValid: true,
            error: null,
        };

        let invalidOptions = false;

        if (options.length === 0) {
            invalidOptions = true;
        }

        let detailsMessage = '';
        let optionIndex = 0;
        const optionNames = [];
        const optionLabels = [];

        options.forEach((option) => {
            optionIndex++;

            if (! option.name?.trim()) {
                invalidOptions = true;
                detailsMessage = sprintf(
                    // translators: %s is the option name.
                    __('The option "%s" does not have a name.', 'post-expirator'),
                    optionIndex
                );
            }

            if (! option.label.trim()) {
                invalidOptions = true;
                detailsMessage = sprintf(
                    // translators: %s is the option name.
                    __('The option "%s" does not have a label.', 'post-expirator'),
                    optionIndex
                );
            }

            if (optionNames.includes(option.name)) {
                invalidOptions = true;
                detailsMessage = sprintf(
                    // translators: %s is the option name.
                    __('The option "%s" has a duplicate name.', 'post-expirator'),
                    option.name
                );
            }

            if (optionLabels.includes(option.label)) {
                invalidOptions = true;
                detailsMessage = sprintf(
                    // translators: %s is the option label.
                    __('The option "%s" has a duplicate label.', 'post-expirator'),
                    option.label
                );
            }

            optionNames.push(option.name);
            optionLabels.push(option.label);
        });

        if (invalidOptions) {
            return {
                isValid: false,
                error: sprintf(
                    // translators: %s is the field label.
                    __('The field %s must be a valid list of options.', 'post-expirator'),
                    ruleData?.fieldLabel
                ),
                details: detailsMessage,
            };
        }

        return successfulResult;
    }, []);

    const validateNodes = useCallback((nodes, edges, nodeSlugs) => {
        nodes.forEach((node) => {
            const nodeType = getNodeTypeByName(node.data?.name);
            const nodeSettings = node.data?.settings || {};
            const settingsSchema = nodeType?.settingsSchema;
            const validationSchema = nodeType?.validationSchema;

            resetNodeErrors(node.id);

            if (! validationSchema) {
                return;
            }

            if (validationSchema?.connections?.rules) {
                validationSchema.connections.rules.forEach((ruleData) => {
                    switch(ruleData.rule) {
                        case 'hasIncomingConnection':
                            if (!nodeHasIncomers(node)) {
                                addNodeError(
                                    node.id,
                                    'no-incomers',
                                    __('This step requires a connection from a previous step.', 'post-expirator')
                                );
                            }
                            break;

                        case 'hasOutgoingConnection':
                            if (!nodeHasOutgoers(node)) {
                                addNodeError(
                                    node.id,
                                    'no-outgoers',
                                    __('This step requires a connection to a following step.', 'post-expirator')
                                );
                            }
                            break;

                        case 'hasIncomerOfName':
                            const allIncomers = getNodeIncomersRecursively(node);

                            let hasError = false;

                            if (allIncomers.length === 0) {
                                hasError = true;
                            } else {
                                var hasIncomerOfName = false;
                                allIncomers.forEach((incomer) => {
                                    if (incomer.data?.name === ruleData.name) {
                                        hasIncomerOfName = true;
                                    }
                                });

                                hasError = !hasIncomerOfName;
                            }

                            if (hasError) {
                                addNodeError(
                                    node.id,
                                    'parent-name',
                                    ruleData.message,
                                );
                            }
                            break;
                    }
                });
            }

            if (validationSchema?.settings?.rules) {
                validationSchema.settings.rules.forEach((ruleData) => {
                    const rule = ruleData.rule;
                    const fieldName = ruleData?.field || '';
                    const fieldNames = fieldName?.split('.') || [];
                    const fieldLabel = ruleData?.label || settingsSchema.find((panel) => panel?.fields.find((field) => field.name === fieldNames[0]))?.label;

                    let settingValue = nodeSettings;
                    for (let i = 0; i < fieldNames.length; i++) {
                        settingValue = settingValue?.[fieldNames[i]];
                    }

                    switch(rule) {
                        case 'required':
                            if (ruleData?.condition) {
                                const conditionField = ruleData.condition.field;
                                const conditionValue = ruleData.condition.value;

                                let conditionSettingValue = nodeSettings;
                                for (let i = 0; i < conditionField.split('.').length; i++) {
                                    conditionSettingValue = conditionSettingValue?.[conditionField.split('.')[i]];
                                }

                                if (conditionSettingValue == conditionValue && (!settingValue || settingValue == '')) {
                                    addNodeError(
                                        node.id,
                                        `${fieldName}-required-if`,
                                        sprintf(
                                            __('The field %s is required.', 'post-expirator'),
                                            fieldLabel
                                        )
                                    );
                                }
                            } else {
                                const isEmpty = (value) => {
                                    return value === ''
                                        || value === null
                                        || value === undefined
                                        || (Array.isArray(value) && value.length === 0)
                                        // If the default value is an object with a rule, that is the default value
                                        // and it was not set by the user yet.
                                        || (typeof value === 'object' && value.rule);
                                };

                                if (isEmpty(settingValue)) {
                                    addNodeError(
                                        node.id,
                                        `${fieldName}-required`,
                                        sprintf(
                                            __('The field %s is required.', 'post-expirator'),
                                            fieldLabel
                                        )
                                    );
                                    break;
                                }
                            }
                            break;

                        case 'dataType':
                            const type = ruleData.type;

                            if (settingValue === undefined || settingValue === null || settingValue === '') {
                                return;
                            }

                            if (type === 'email') {
                                if (! isEmail(settingValue)) {
                                    addNodeError(
                                        node.id,
                                        `${fieldName}-email`,
                                        sprintf(
                                            __('The field %s must be a valid email address.', 'post-expirator'),
                                            fieldLabel
                                        )
                                    );
                                }
                            } else if (type === 'emailList') {
                                const emails = settingValue.split(',');
                                let email;
                                for (let i = 0; i < emails.length; i++) {
                                    email = emails[i].trim();

                                    if (!isEmail(email) && ! isVariable(email)) {
                                        addNodeError(
                                            node.id,
                                            `${fieldName}-emailList`,
                                            sprintf(
                                                __('The field %s must be a valid email address list separated by commas.', 'post-expirator'),
                                                fieldLabel
                                            )
                                        );

                                        break;
                                    }
                                }
                            } else if (type === 'integer') {
                                if (!isInt(settingValue)) {
                                    addNodeError(
                                        node.id,
                                        `${fieldName}-integer`,
                                        sprintf(
                                            __('The field %s must be an integer.', 'post-expirator'),
                                            fieldLabel
                                        )
                                    );
                                }
                            } else if (type === 'integerList') {
                                let integer;
                                for (let i = 0; i < settingValue.length; i++) {
                                    integer = settingValue[i].trim();

                                    if (!isInt(integer)) {
                                        addNodeError(
                                            node.id,
                                            `${fieldName}-integerList`,
                                            sprintf(
                                                __('The field %s must be an integer list separated by commas.', 'post-expirator'),
                                                fieldLabel
                                            )
                                        );

                                        break;
                                    }
                                }
                            } else if (type === 'nameValuePairList') {
                                if (! settingValue) {
                                    return;
                                }

                                const property = ruleData.field;

                                if (! settingValue[property]) {
                                    return;
                                }

                                if (! Array.isArray(settingValue[property])) {
                                    addNodeError(
                                        node.id,
                                        `${fieldName}-nameValuePairList`,
                                        sprintf(__('The field %s must be a list of name-value pairs.', 'post-expirator'), fieldLabel)
                                    );
                                }

                                settingValue[property].forEach((item, i) => {
                                    if (item?.name === '') {
                                        addNodeError(
                                            node.id,
                                            `${fieldName}-nameValuePairList`,
                                            sprintf(__('The field %s must be a list of name-value pairs.', 'post-expirator'), fieldLabel),
                                            sprintf(__('The name of the pair is required on item %d.', 'post-expirator'), i + 1)
                                        );
                                    }

                                    if (item?.value === '') {
                                        addNodeError(
                                            node.id,
                                            `${fieldName}-nameValuePairList`,
                                            sprintf(__('The field %s must be a list of name-value pairs.', 'post-expirator'), fieldLabel),
                                            sprintf(__('The value of the pair is required on item %d.', 'post-expirator'), i + 1)
                                        );
                                    }
                                });
                            }

                            break;
                        case 'validVariable':
                            const variableValidation = isVariableValid(node, settingValue, ruleData);

                            if (!variableValidation.isValid) {
                                addNodeError(
                                    node.id,
                                    `${fieldName}-validVariable`,
                                    variableValidation.error,
                                    variableValidation.details
                                );
                            }
                            break;

                        case 'validExpression':
                            const expressionValidation = isExpressionValid(settingValue, ruleData);

                            if (!expressionValidation.isValid) {
                                addNodeError(
                                    node.id,
                                    `${fieldName}-validExpression`,
                                    expressionValidation.error,
                                    expressionValidation.details
                                );
                            }
                            break;

                        case 'validOptions':
                            if (! Array.isArray(settingValue)) {
                                addNodeError(node.id, `${fieldName}-validOptions`, sprintf(__('The field %s must be a list of options.', 'post-expirator'), fieldLabel));
                            }

                            const optionsValidation = isOptionsValid(settingValue, ruleData);

                            if (!optionsValidation.isValid) {
                                addNodeError(
                                    node.id,
                                    `${fieldName}-validOptions`,
                                    optionsValidation.error,
                                    optionsValidation.details
                                );
                            }

                            break;

                        case 'hasVariableSyntax':
                            if (!settingValue || settingValue === '') {
                                break;
                            }

                            const trimmedValue = settingValue.trim();
                            const hasProperSyntax = trimmedValue.startsWith('{{') && trimmedValue.endsWith('}}');

                            if (!hasProperSyntax) {
                                addNodeError(
                                    node.id,
                                    `${fieldName}-hasVariableSyntax`,
                                    sprintf(
                                        __('Invalid variable.', 'post-expirator'),
                                        fieldLabel
                                    ),
                                    __('Please use the variable picker to select valid expression.', 'post-expirator')
                                );
                            }
                            break;
                    }
                });
            }
        });
    }, [getNodeTypeByName, addNodeError, resetNodeErrors, isExpressionValid]);

    const debounceValidation = useDebounce((nodes, edges, nodeSlugs) => {
        validateNodes(nodes, edges, nodeSlugs);
    }, DEBOUNCE_TIME);

    useEffect(() => {
        debounceValidation(nodes, edges, nodeSlugs);

        return () => {
            debounceValidation.cancel();
        };
    }, [nodes, edges, nodeSlugs, debounceValidation]);

    return;
}

export default NodeValidator;
