import { __ } from "@wordpress/i18n";

export const HelpLink = ({ url, label }) => {
    if (!label) {
        label = __('Learn more', 'post-expirator');
    }

    return (
        <a href={url} target="_blank" rel="noopener noreferrer">{label}</a>
    );
}
