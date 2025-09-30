import { useState, useCallback } from '@wordpress/element';
import { parseJsonLogic } from 'react-querybuilder/parseJsonLogic';
import { formatQuery, defaultOperators, defaultRuleProcessorJsonLogic, defaultRuleProcessorNL } from 'react-querybuilder';

export const useConditionalLogic = ({ defaultValue, name, onChange, variables }) => {

    /**
     * Extend the default operators with our custom ones.
     * "has" and "doesNotHave" are just flipped versions of "in" / "not in",
     * but written so the value comes first and the field comes second.
     */
    const customOperators = [
        ...defaultOperators,
        {
            name: 'has',
            value: 'has',
            label: 'has',
            jsonLogic: (field, value) => ({ in: [value, { var: field }] }),
            formatValue: (_field, value) => `'${value}'`,
        },
        {
            name: 'doesNotHave',
            value: 'doesNotHave',
            label: 'does not have',
            jsonLogic: (field, value) => ({ '!': { in: [value, { var: field }] } }),
            formatValue: (_field, value) => `'${value}'`,
        },
    ];

    /**
     * Map JSONLogic back into querybuilder rules.
     * Without this, parseJsonLogic wouldn’t know how to turn {in: [...]} or {!:{in: [...]}}
     * into our custom operators and the data won't be included in the flow json.
     */
    const jsonLogicOperations = {
        // Handle "has" => { in: [value, { var: field }] }
        in: ([value, field]) => ({
            field: field.var,
            operator: 'has',
            value,
        }),
        // Handle "! in" => "doesNotHave"
        "!": (arg) => {
            if (arg.in) {
            const [value, field] = arg.in;
            return {
                field: field.var,
                operator: 'doesNotHave',
                value,
            };
            }
        },
    };

    /**
     *  Override natural language formatting for our custom operators.
     * This ensures the text reads naturally ("Category does not have 'apple'")
     * instead of "Category doesNotHave 'apple'".
     */
    const customRuleProcessorNL = useCallback((rule, options) => {

        const fieldObject = options.fields.find(field => field.name === rule.field);
        const fieldLabel = fieldObject ? fieldObject.label : rule.field;

        const formattedValue = `'${rule.value}'`;

        if (rule.operator === 'doesNotHave') {
            return `${fieldLabel} does not have ${formattedValue}`;
        } else if (rule.operator === 'has') {
            return `${fieldLabel} has ${formattedValue}`;
        }

        return defaultRuleProcessorNL(rule, options);
    }, []);


    // Initialize query state from JSONLogic, using our custom operation mapping
    const [query, setQuery] = useState(
        parseJsonLogic(defaultValue?.json || '', { jsonLogicOperations })
    );

    /**
     * Format condition for saving/export.
     * Produces both JSONLogic (for machine rules) and natural language (for display).
     */
    const formatCondition = useCallback(() => {
        /**
         * This ensures that when we export to JSONLogic,
         * our custom operators output the right structure.
         * @param {*} rule
         * @param {*} options
         * @returns
         */
        const customRuleProcessor = (rule, options) => {
            const op = customOperators.find(o => o.name === rule.operator);
            if (op?.jsonLogic) {
                return op.jsonLogic(rule.field, rule.value);
            }
            return defaultRuleProcessorJsonLogic(rule, options);
        };

        const jsonCondition = formatQuery(query, {
            format: 'jsonlogic',
            parseNumbers: true,
            ruleProcessor: customRuleProcessor
        });

        const naturalLanguageCondition = formatQuery(query, {
            format: 'natural_language',
            parseNumbers: true,
            fields: variables,
            operators: customOperators,
            ruleProcessor: customRuleProcessorNL,
        });

        return {
            ...defaultValue,
            json: jsonCondition,
            natural: naturalLanguageCondition,
        };
    }, [query, defaultValue, onChange, name, variables]);

    return { query, setQuery, formatCondition, operators: customOperators, customRuleProcessorNL };
};
