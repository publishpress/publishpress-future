import {
    Button,
    Modal,
    __experimentalHStack as HStack,
    __experimentalHeading as Heading,
    TextareaControl
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useState, useRef, useCallback, useEffect } from "@wordpress/element";
import NodeIcon from "../../node-icon";
import ColumnsContainer from "./columns-container";
import { DescriptionText } from "../description-text";
import ace, {Ace} from "ace-builds";
import AceEditor from "react-ace";
import "ace-builds/src-noconflict/mode-handlebars";
import "ace-builds/src-noconflict/theme-textmate";
import "ace-builds/src-noconflict/ext-language_tools";
import { ModalFooter } from './../modal-footer'

import './style.css';

export const ExpressionBuilder = ({
    name,
    label = '',
    defaultValue,
    onChange,
    variables = [],
    propertyName = "expression",
    settings = {},
    description = '',
    isInline = false,
    readOnlyPreview = false,
    singleVariableOnly = false,
    wrapOnPreview = false,
    wrapOnEditor = false,
    oneLinePreview = false,
    helpUrl = '',
    autoComplete = true,
    completers = [],
    buttonText = null,
}) => {
    const editorFullRef = useRef(null);
    const editorSmallRef = useRef(null);

    const [currentDescription, setCurrentDescription] = useState();
    const [currentVariableId, setCurrentVariableId] = useState();
    const [isOpen, setIsOpen] = useState(false);

    if (! defaultValue) {
        defaultValue = {};
    }

    const onChangeSetting = useCallback(({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }

        // Example: Basic linting logic (you can integrate a real linter here)
        const annotations = [];
        if (value.includes('error')) {
            annotations.push({
                row: 0,
                column: 10,
                text: "Example error: 'error' found in code",
                type: "error"
            });
        }

        if (editorFullRef.current) {
            // editorFullRef.current.editor.getSession().setAnnotations(annotations);
        }
    }, [defaultValue]);

    const onClose = useCallback(() => {
        setIsOpen(false);
    }, [setIsOpen]);

    const onDoubleClick = useCallback((item) => {
        if (editorFullRef.current) {
            const editor = editorFullRef.current.editor;

            if (! singleVariableOnly) {
                const cursorPosition = editor.getCursorPosition();
                editor.session.insert(cursorPosition, item.id);
            } else {
                editor.session.setValue(item.id);
            }

            editor.focus();

            if (singleVariableOnly) {
                setIsOpen(false);
            }
        }
    }, [editorFullRef, singleVariableOnly]);

    const editorProps = {
        $blockScrolling: true,
    };

    useEffect(() => {
        if (wrapOnPreview && editorSmallRef.current) {
            editorSmallRef.current.editor.setOption("indentedSoftWrap", false);
        }
    }, [wrapOnPreview, editorSmallRef]);

    const expression = (defaultValue[propertyName] || '').toString();
    const placeholder = (settings?.placeholder || '').toString();

    useEffect(() => {
        if (completers.length === 0) {
            return;
        }

        // Set completers for each editor instance individually
        if (editorFullRef.current) {
            const editor = editorFullRef.current.editor;
            editor.completers = completers;
        }

        if (editorSmallRef.current) {
            const editor = editorSmallRef.current.editor;
            editor.completers = completers;
        }
    }, [completers, editorFullRef, editorSmallRef]);

    return <div className={`expression-builder ${isOpen ? 'expression-builder-open' : ''} ${isInline ? 'expression-builder-inline' : ''}`}>

        <Button
            variant="secondary"
            onClick={() => setIsOpen(true)}
            className="expression-builder-button"
            title={buttonText || __("Edit", "post-expirator")}
        >
            {buttonText || __("Edit", "post-expirator")}
        </Button>

        {! isInline && label && (
            <Heading level={3} className="expression-builder-small-heading">{label}</Heading>
        )}

        <AceEditor
            ref={editorSmallRef}
            mode="handlebars"
            theme="textmate"
            name="expression-builder-small"
            value={expression}
            readOnly={readOnlyPreview}
            className={readOnlyPreview ? 'read-only-editor' : ''}
            editorProps={editorProps}
            wrapEnabled={wrapOnPreview}
            onChange={(value) => onChangeSetting({ settingName: propertyName, value })}
            setOptions={{
                enableBasicAutocompletion: autoComplete,
                enableLiveAutocompletion: autoComplete,
                showGutter: false,
                showPrintMargin: false,
                showLineNumbers: false,
                highlightActiveLine: false,
            }}
            height={oneLinePreview ? '30px' : '92px'}
            width="100%"
            placeholder={placeholder}
        />

        {description && (
            <DescriptionText text={description} helpUrl={helpUrl} />
        )}

        {isOpen && (
            <Modal
                title={label}
                onRequestClose={onClose}
                className="workflow-editor-modal expression-builder-modal"
            >
                <div style={{ minWidth: '600px', maxWidth: '600px' }}>
                    {singleVariableOnly && (
                        <p>{__("Select a variable from the list below.", "post-expirator")}</p>
                    )}

                    {!singleVariableOnly && (
                        <p>{__("Type your expression here or use the variables below.", "post-expirator")}</p>
                    )}

                    <AceEditor
                        ref={editorFullRef}
                        mode="handlebars"
                        theme="textmate"
                        name="expression-builder-full"
                        className={singleVariableOnly ? 'read-only-editor' : ''}
                        wrapEnabled={wrapOnEditor}
                        onChange={(value) => onChangeSetting({ settingName: propertyName, value })}
                        value={expression}
                        editorProps={editorProps}
                        readOnly={singleVariableOnly}
                        setOptions={{
                            enableBasicAutocompletion: autoComplete,
                            enableLiveAutocompletion: autoComplete,
                            showLineNumbers: !singleVariableOnly,
                            showGutter: !singleVariableOnly,
                            highlightActiveLine: !singleVariableOnly,
                        }}
                        height={singleVariableOnly ? '30px' : '35px'}
                        width="100%"
                        placeholder={placeholder}
                    />

                    <div className="expression-builder-modal-variables" style={{ maxWidth: '600px', overflowX: 'auto' }}>
                        <Heading level={2} className="components-truncate components-text components-heading block-editor-inspector-popover-header__heading">{__("Variables", "post-expirator")}</Heading>

                        {singleVariableOnly && (
                            <p>{__("Double-click on a variable to select it.", "post-expirator")}</p>
                        )}

                        {! singleVariableOnly && (
                            <p>{__("Double-click on any variable to add it to your expression.", "post-expirator")}</p>
                        )}

                        {currentDescription && (
                            <p className="description margin-top">
                                <code className="expression-builder-variable-name">
                                    {currentVariableId}
                                </code> {currentDescription}
                            </p>
                        )}

                        {!currentDescription && (
                            <p className="description margin-top">{__("Hover over a variable to see its description.", "post-expirator")}</p>
                        )}

                        <ColumnsContainer
                            items={variables}
                            setCurrentDescription={setCurrentDescription}
                            setCurrentVariableId={setCurrentVariableId}
                            onDoubleClick={onDoubleClick}
                        />
                    </div>
                </div>
                <ModalFooter onClose={ onClose } />
            </Modal>
        )}
    </div>;
}

export default ExpressionBuilder;
