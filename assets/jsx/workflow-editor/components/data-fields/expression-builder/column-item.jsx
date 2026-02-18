import { __, sprintf } from "@publishpress/i18n";
import { useState } from "@wordpress/element";
import { TextControl, Button } from "@wordpress/components";

const TYPE_ICON_MAP = {
    string:         { dashiconClass: 'dashicons-editor-quote',  title: 'String' },
    integer:        { svgIcon: 'hash',                           title: 'Integer' },
    boolean:        { dashiconClass: 'dashicons-yes-alt',       title: 'Boolean' },
    array:          { dashiconClass: 'dashicons-list-view',     title: 'Array' },
    object:         { dashiconClass: 'dashicons-category',      title: 'Object' },
    datetime:       { dashiconClass: 'dashicons-calendar-alt',  title: 'Date/Time' },
    url:            { dashiconClass: 'dashicons-editor-quote',  title: 'URL' },
    email:          { dashiconClass: 'dashicons-editor-quote',  title: 'Email' },
    post:           { dashiconClass: 'dashicons-category',      title: 'Post' },
    user:           { dashiconClass: 'dashicons-category',      title: 'User' },
    meta:           { dashiconClass: 'dashicons-editor-quote',  title: 'Metadata' },
    post_status:    { dashiconClass: 'dashicons-editor-quote',  title: 'Post Status' },
    post_type:      { dashiconClass: 'dashicons-editor-quote',  title: 'Post Type' },
    post_terms:     { dashiconClass: 'dashicons-category',      title: 'Post Terms' },
    taxonomy_terms: { dashiconClass: 'dashicons-category',      title: 'Taxonomy Terms' },
    user_roles:     { dashiconClass: 'dashicons-list-view',     title: 'User Roles' },
    node:           { dashiconClass: 'dashicons-controls-play', title: 'Step' },
    'future-action':{ dashiconClass: 'dashicons-category',      title: 'Future Action' },
    site:           { dashiconClass: 'dashicons-admin-site',    title: 'Site' },
};

const getTypeIcon = (type) => {
    if (type && TYPE_ICON_MAP[type]) {
        return TYPE_ICON_MAP[type];
    }
    return { dashiconClass: 'dashicons-marker', title: type || 'Variable' };
};

const SVG_ICONS = {
    hash: (
        <svg viewBox="0 0 16 16" width="14" height="14" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" xmlns="http://www.w3.org/2000/svg">
            <line x1="5.5" y1="1.5" x2="4.5" y2="14.5" />
            <line x1="11.5" y1="1.5" x2="10.5" y2="14.5" />
            <line x1="1.5" y1="5.5" x2="14.5" y2="5.5" />
            <line x1="1.5" y1="10.5" x2="14.5" y2="10.5" />
        </svg>
    ),
};

const TypeIcon = ({ iconDef }) => {
    if (!iconDef) return null;

    const { dashiconClass, svgIcon, title } = iconDef;

    if (svgIcon && SVG_ICONS[svgIcon]) {
        return (
            <span className="column-item-type-icon column-item-type-icon-svg" title={title}>
                {SVG_ICONS[svgIcon]}
            </span>
        );
    }

    return (
        <span
            className={`dashicons column-item-type-icon ${dashiconClass}`}
            title={title}
            aria-hidden="true"
        />
    );
};

const ColumnItemMeta = ({ item, onClick }) => {
    const [metaKey, setMetaKey] = useState('');

    let metaDescription = sprintf(
        /* translators: %s is the database table name */
        __('Type the %s key and click on the button to insert it.', 'post-expirator'),
        item.context?.table || 'meta'
    );

    const metaItem = {
        id: `{{${item.name}.${metaKey}}}`,
        name: item.name + '.' + metaKey,
        label: __('Metadata key', 'post-expirator'),
        description: metaDescription,
        context: item.context
    }

    return (
        <div className="column-item-form">
            <TextControl
                label={item.label}
                value={metaKey}
                onChange={(value) => setMetaKey(value)}
                help={item.description}
            />
            <Button variant="secondary" onClick={() => {onClick(metaItem)}}>
                {__('Insert', 'post-expirator')}
            </Button>
        </div>
    );
}

const ColumnItemVariable = ({
    item,
    currentItemPath,
    onClick,
    setCurrentDescription,
    setCurrentVariableId,
    onDoubleClick,
    path = [],
    index,
    columnIndex
}) => {
    const hasChildren = item.children && item.children.length > 0;
    const currentColumnIndex = path.length - 1;
    const selectedItemIndex = currentItemPath[currentColumnIndex];

    const onMouseEnter = () => {
        setCurrentDescription(`${item.description}`);
        setCurrentVariableId(item.id);
    }

    const stepSlug = item.name.split('.')[0];
    const stepSlugLabel = stepSlug ? `(${stepSlug})` : '';
    const showStepSlugLabel = columnIndex === 0 && stepSlug !== 'global';

    const iconDef = getTypeIcon(item.type);

    return <div
        className={`column-item ${selectedItemIndex === index ? 'selected' : ''} ${hasChildren ? 'has-children' : ''}`}
        onClick={() => onClick(path, currentColumnIndex, index)}
        onMouseEnter={onMouseEnter}
        onDoubleClick={() => onDoubleClick(item)}
    >
        <TypeIcon iconDef={iconDef} />
        {item.label} {showStepSlugLabel ? <span className="column-item-step-slug">{stepSlugLabel}</span> : ''}
    </div>;
};

export const ColumnItem = ({
    item,
    currentItemPath,
    onClick,
    setCurrentDescription,
    setCurrentVariableId,
    onDoubleClick,
    path = [],
    index,
    columnIndex
}) => {
    if (item?.type === 'meta-key-input') {
        return <ColumnItemMeta item={item} onClick={onDoubleClick} />;
    }

    return <ColumnItemVariable
        item={item}
        currentItemPath={currentItemPath}
        onClick={onClick}
        setCurrentDescription={setCurrentDescription}
        setCurrentVariableId={setCurrentVariableId}
        onDoubleClick={onDoubleClick}
        path={path}
        index={index}
        columnIndex={columnIndex}
    />;
};

export default ColumnItem;
