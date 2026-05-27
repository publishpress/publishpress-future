import { __ } from '@wordpress/i18n';
import { withConditional } from '../../conditional';
import { PostFieldSelector } from './post-field-selector';
import { PostValueSelector } from './post-value-selector';

import { parseJsonLogic } from 'react-querybuilder/parseJsonLogic';
import { formatQuery, defaultOperators } from 'react-querybuilder';

import { queryFields } from './query-fields';

const convertLegacySettingsIntoJson = (defaultValue) => {
    const { postType, postStatus, postId } = defaultValue;

    // Helper function to create clauses for each field type
    const createClauses = (values, fieldPath) => {
        if (!values || values.length === 0) return null;

        const clauses = values.map(value => ({
            "==": [
                { "var": `post.${fieldPath}` },
                value
            ]
        }));

        return { "or": clauses };
    };

    let clauses = [];
    let json = {};

    // Add post type conditions
    const typeClause = createClauses(postType, 'post_type');
    if (typeClause) {
        clauses.push(typeClause)
    };

    // Add post status conditions
    const statusClause = createClauses(postStatus, 'post_status');
    if (statusClause) {
        clauses.push(statusClause)
    };

    // Add post ID conditions
    const idClause = createClauses(postId, 'ID');
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
 * Post Search Query component for filtering posts
 */
const PostSearchQuery = (props) => {
    if (! props.defaultValue.json) {
        const jsonValue = convertLegacySettingsIntoJson(props.defaultValue);

        const query = parseJsonLogic(jsonValue);

        props.defaultValue.json = formatQuery(query, {
            format: 'jsonlogic',
            parseNumbers: true,
            fields: queryFields,
        });

        props.defaultValue.natural = formatQuery(query, {
            format: 'natural_language',
            parseNumbers: true,
            fields: queryFields,
            getOperators: () => defaultOperators,
        });
    }

    return withConditional({
        FieldComponent: PostFieldSelector,
        ValueComponent: PostValueSelector,
        modalTitle: __('Post Search Query', 'post-expirator'),
        modalDescription: __('Create a search query to filter posts based on conditions.', 'post-expirator'),
        buttonText: __('Edit query', 'post-expirator'),
        defaultField: '',
        queryFields: queryFields,
    })(props);
};

export default PostSearchQuery;
