import { __ } from '@wordpress/i18n';

export const queryFields = [
    {
        label: __('Post ID', 'post-expirator'),
        value: 'p.ID',
    },
    {
        label: __('Post Title', 'post-expirator'),
        value: 'p.post_title',
    },
    {
        label: __('Post Content', 'post-expirator'),
        value: 'p.post_content',
    },
    {
        label: __('Post Excerpt', 'post-expirator'),
        value: 'p.post_excerpt',
    },
    {
        label: __('Post Author', 'post-expirator'),
        value: 'p.post_author',
    },
    {
        label: __('Post Date', 'post-expirator'),
        value: 'p.post_date',
    },
    {
        label: __('Post Status', 'post-expirator'),
        value: 'p.post_status',
    },
    {
        label: __('Post Type', 'post-expirator'),
        value: 'p.post_type',
    },
    {
        label: __('Post Parent', 'post-expirator'),
        value: 'p.post_parent',
    },
];
