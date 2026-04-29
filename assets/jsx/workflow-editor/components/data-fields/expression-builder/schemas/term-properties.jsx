import { __ } from "@wordpress/i18n";

export const termProperties = [
    {
        name: "name",
        type: "string",
        label: __("Name", "post-expirator"),
        description: __("The name of the term.", "post-expirator"),
    },
    {
        name: "slug",
        type: "string",
        label: __("Slug", "post-expirator"),
        description: __("The slug of the term.", "post-expirator"),
    },
    {
        name: "term_id",
        type: "integer",
        label: __("Term ID", "post-expirator"),
        description: __("The unique identifier of the term.", "post-expirator"),
    },
    {
        name: "count",
        type: "integer",
        label: __("Count", "post-expirator"),
        description: __("Number of posts assigned to this term.", "post-expirator"),
    }
];
