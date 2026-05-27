import { __ } from '@wordpress/i18n';
import { withConditional } from '../../conditional';
import { PostFieldSelector } from './post-field-selector';
import { PostValueSelector } from './post-value-selector';

import { parseJsonLogic } from 'react-querybuilder/parseJsonLogic';
import { formatQuery, defaultOperators } from 'react-querybuilder';

const convertLegacySettingsIntoJson = (defaultValue, firstPostVariable) => {
    const { postType, postStatus, postId } = defaultValue;

    // Helper function to create clauses for each field type
    const createClauses = (values, fieldPath) => {
        if (!values || values.length === 0) return null;

        const clauses = values.map(value => ({
            "==": [
                { "var": `{{${firstPostVariable}.${fieldPath}}}` },
                value
            ]
        }));

        return { "or": clauses };
    };

    let clauses = [];
    let json = {};

    // Add post type conditions
    const typeClause = createClauses(postType, 'type');
    if (typeClause) {
        clauses.push(typeClause)
    };

    // Add post status conditions
    const statusClause = createClauses(postStatus, 'status');
    if (statusClause) {
        clauses.push(statusClause)
    };

    // Add post ID conditions
    const idClause = createClauses(postId, 'id');
    if (idClause) {
        clauses.push(idClause)
    };

    if (clauses.length > 1) {
        json = { "and": clauses };
    } else {
        json = clauses[0];
    }

    return json;
};

/**
 * Post Filter component for creating query filters
 */
const PostFilter = (props) => {
    if (! props.defaultValue.json) {
        // Look for the first post variable in the step scoped variables
        const firstPostVariable = props.stepScopedVariables.find(variable => variable.type === 'post');
        const jsonValue = convertLegacySettingsIntoJson(props.defaultValue, firstPostVariable.name);

        const query = parseJsonLogic(jsonValue);

        props.defaultValue.json = formatQuery(query, {
            format: 'jsonlogic',
            parseNumbers: true,
        });

        props.defaultValue.natural = formatQuery(query, {
            format: 'natural_language',
            parseNumbers: true,
            fields: props.variables,
            getOperators: () => defaultOperators,
        });
    }

    return withConditional({
        FieldComponent: PostFieldSelector,
        ValueComponent: PostValueSelector,
        modalTitle: __('Post Filter', 'post-expirator'),
        modalDescription: __('Create filters to query specific posts based on rules.', 'post-expirator'),
        buttonText: __('Edit rules', 'post-expirator'),
        defaultField: '',
    })(props);
};

export default PostFilter;
