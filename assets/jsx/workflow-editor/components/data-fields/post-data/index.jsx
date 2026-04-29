import { __ } from "@wordpress/i18n";
import { __experimentalVStack as VStack } from "@wordpress/components";
import { PostDateControl } from "./post-date";
import { PostTextControl } from "./post-text";
import { PostAuthorControl } from "./post-author";
import { PostDiscussionControl } from "./post-discussion";

export function PostData({ name, label, defaultValue, onChange, settings, variables }) {
    const onChangeSetting = ({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }

    const onClosePopover = (settingName) => {
        if (! defaultValue[settingName]?.update) {
            onChangeSetting({ settingName, value: null });
        }
    }

    return (
        <VStack className="workflow-editor-panel">
            <PostDateControl
                name="date"
                label={__('Post Date', 'post-expirator')}
                defaultValue={defaultValue?.date}
                onChange={(settingName, value) => onChangeSetting({ settingName: "date", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("date")}
                checkboxLabel={__("Update the post date", "post-expirator")}
            />
            <PostTextControl
                name="title"
                label={__('Post Title', 'post-expirator')}
                defaultValue={defaultValue?.title}
                onChange={(settingName, value) => onChangeSetting({ settingName: "title", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("title")}
                checkboxLabel={__("Update the post title", "post-expirator")}
            />
            <PostTextControl
                name="name"
                label={__('Post Slug', 'post-expirator')}
                defaultValue={defaultValue?.name}
                onChange={(settingName, value) => onChangeSetting({ settingName: "name", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("name")}
                checkboxLabel={__("Update the post slug", "post-expirator")}
            />
            <PostTextControl
                name="content"
                label={__('Post Content', 'post-expirator')}
                defaultValue={defaultValue?.content}
                onChange={(settingName, value) => onChangeSetting({ settingName: "content", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("content")}
                checkboxLabel={__("Update the post content", "post-expirator")}
            />
            <PostTextControl
                name="excerpt"
                label={__('Post Excerpt', 'post-expirator')}
                defaultValue={defaultValue?.excerpt}
                onChange={(settingName, value) => onChangeSetting({ settingName: "excerpt", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("excerpt")}
                checkboxLabel={__("Update the post excerpt", "post-expirator")}
            />
            <PostDiscussionControl
                name="discussion"
                label={__('Post Discussion', 'post-expirator')}
                defaultValue={defaultValue?.discussion}
                onChange={(settingName, value) => onChangeSetting({ settingName: "discussion", value })}
                onClosePopover={() => onClosePopover("discussion")}
                checkboxLabel={__("Update the post discussion", "post-expirator")}
            />
            <PostTextControl
                name="password"
                label={__('Post Password', 'post-expirator')}
                defaultValue={defaultValue?.password}
                onChange={(settingName, value) => onChangeSetting({ settingName: "password", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("password")}
                checkboxLabel={__("Update the post password", "post-expirator")}
            />
            <PostAuthorControl
                name="author"
                label={__('Post Author', 'post-expirator')}
                defaultValue={defaultValue?.author}
                onChange={(settingName, value) => onChangeSetting({ settingName: "author", value })}
                settings={settings}
                variables={variables}
                onClosePopover={() => onClosePopover("author")}
                checkboxLabel={__("Update the post author", "post-expirator")}
            />
        </VStack>
    );
}

export default PostData;
