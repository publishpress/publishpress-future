import {
    Button,
    Modal,
    __experimentalHStack as HStack,
    __experimentalHeading as Heading,
    TextareaControl
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useState, useRef, useCallback, useEffect, createPortal } from "@wordpress/element";
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

const HOVER_HINT_DELAY = 700;

const VariableHoverHint = ({ hint }) => createPortal(
    <div
        className="expression-builder-variable-hover-hint"
        style={{ top: hint.top, left: hint.left }}
    >
        <code>{hint.item.id}</code>
        {hint.item.description ? ` ${hint.item.description}` : ''}
    </div>,
    document.body
);

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
    const hoverHintTimeoutRef = useRef(null);

    const [isOpen, setIsOpen] = useState(false);
    const [hoverHint, setHoverHint] = useState(null);

    const clearHoverHintTimeout = useCallback(() => {
        if (hoverHintTimeoutRef.current) {
            clearTimeout(hoverHintTimeoutRef.current);
            hoverHintTimeoutRef.current = null;
        }
    }, []);

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
        clearHoverHintTimeout();
        setIsOpen(false);
        setHoverHint(null);
    }, [clearHoverHintTimeout, setIsOpen]);

    const onVariableHover = useCallback((item, event) => {
        clearHoverHintTimeout();
        const rect = event.currentTarget.getBoundingClientRect();
        const hint = { item, top: rect.top, left: rect.right + 8 };

        hoverHintTimeoutRef.current = setTimeout(() => {
            setHoverHint(hint);
            hoverHintTimeoutRef.current = null;
        }, HOVER_HINT_DELAY);
    }, [clearHoverHintTimeout]);

    const onVariableHoverEnd = useCallback(() => {
        clearHoverHintTimeout();
        setHoverHint(null);
    }, [clearHoverHintTimeout]);

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

    const renderLeafHint = useCallback((item) => (
        <div className="column-leaf-hint-content">
            <p>
                {singleVariableOnly
                    ? __("Click Select to use this variable.", "post-expirator")
                    : __("The variable will be inserted at the current cursor position.", "post-expirator")}
            </p>
            <Button variant="secondary" onClick={() => onDoubleClick(item)}>
                {singleVariableOnly ? __("Select", "post-expirator") : __("Insert", "post-expirator")}
            </Button>
        </div>
    ), [singleVariableOnly, onDoubleClick]);

    const editorProps = {
        $blockScrolling: true,
    };

    useEffect(() => {
        return () => clearHoverHintTimeout();
    }, [clearHoverHintTimeout]);

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
                            <p>{__("Double-click on any variable to insert it into your expression.", "post-expirator")}</p>
                        )}

                        <ColumnsContainer
                            items={variables}
                            onDoubleClick={onDoubleClick}
                            onVariableHover={onVariableHover}
                            onVariableHoverEnd={onVariableHoverEnd}
                            renderLeafHint={renderLeafHint}
                        />

                    </div>
                </div>
                <ModalFooter onClose={ onClose } />
            </Modal>
        )}

        {hoverHint && isOpen && <VariableHoverHint hint={hoverHint} />}
    </div>;
}

export default ExpressionBuilder;
