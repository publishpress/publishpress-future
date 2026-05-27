import { __, sprintf } from "@wordpress/i18n";
import { formatVariableStructure } from "../../../utils";
import { termProperties } from "./schemas/term-properties";

export const typeHandlers = {
    meta: (item) => {

        let metaDescription = sprintf(
            /* translators: %s is the database table name */
            __('Type the %s key and click on the button to insert it.', 'post-expirator'),
            item.context?.table || 'meta'
        );

        return {
            ...item,
            children: [
                {
                    name: item.name,
                    label: __('Metadata key', 'post-expirator'),
                    description: metaDescription,
                    type: 'meta-key-input',
                    context: item.context,
                }
            ]
        }
    },

    post_terms: (item) => {
        const taxonomies = futureWorkflowEditor.taxonomies || [];

        const taxonomyChildren = taxonomies.map(taxonomy =>
            formatVariableStructure({
                ...taxonomy,
                id: `{{${item.name}.${taxonomy.value}}}`,
                name: `${item.name}.${taxonomy.value}`,
                description: sprintf(
                    /* translators: %s is the taxonomy label */
                    __("%s terms for this post.", "post-expirator"),
                    taxonomy.label
                ),
                type: "taxonomy_terms"
            })
        );

        return {
            ...item,
            children: taxonomyChildren
        };
    },

    taxonomy_terms: (item) => {
        const termChildren = termProperties.map(property =>
            formatVariableStructure({
                ...property,
                id: `{{${item.name}.${property.name}}}`,
                name: `${item.name}.${property.name}`
            })
        );

        return {
            ...item,
            children: termChildren
        };
    }
};

export const processItemWithTypeHandler = (item) => {
    const handler = typeHandlers[item?.type];
    return handler ? handler(item) : item;
};
