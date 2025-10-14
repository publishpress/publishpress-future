import { QueryBuilder } from 'react-querybuilder';
import { QueryBuilderDnD } from '@react-querybuilder/dnd';
import { useCallback, useEffect, useMemo } from '@wordpress/element';
import { Button, Modal } from '@wordpress/components';
import { __ } from '@publishpress/i18n';
import { useDispatch } from '@wordpress/data';

import { store as editorStore } from '../../../editor-store';
import { NotToggle } from './not-toggle';
import { AddElementButton } from './add-element-button';
import { RemoveElementButton } from './remove-element-button';
import { DuplicateRuleButton } from './duplicate-rule-button';
import { CombinatorSelector } from './combinator-selector';
import { OperatorSelector } from './operator-selector';
import { ConditionPreview } from './condition-preview';
import { useConditionalLogic } from '../hooks/useConditionalLogic';
import { useModalManagement } from '../hooks/useModalManagement';
import { useEditorSetup } from '../hooks/useEditorSetup';
import { useLegacyVariables } from '../hooks/useLegacyVariables';
import { useIsPro } from '../../../../contexts/pro-context';
import { ModalFooter } from './../../modal-footer'

import 'react-querybuilder/dist/query-builder.css';
import '../../../../css/query-builder.css';
import '../styles/index.css';


const EDITOR_PROPS = {
    $blockScrolling: true,
};

const EDITOR_OPTIONS = {
    enableBasicAutocompletion: false,
    enableLiveAutocompletion: false,
    showGutter: false,
    showPrintMargin: false,
    showLineNumbers: false,
    showInvisibles: false,
    highlightActiveLine: false,
};

const QUERY_BUILDER_TRANSLATIONS = {
    addGroup: { label: __('Add Group', 'post-expirator') },
    addRule: { label: __('Add Rule', 'post-expirator') }
};

const QUERY_BUILDER_CONTROL_CLASSNAMES = {
    queryBuilder: 'queryBuilder-branches',
};

/**
 * Higher-order component for creating conditional query builders
 */
export const withConditional = ({
    FieldComponent,
    ValueComponent,
    modalTitle = __('Condition', 'post-expirator'),
    modalDescription = __('Create rules that will continue the workflow only if certain conditions are met.', 'post-expirator'),
    buttonText = __('Edit rules', 'post-expirator'),
    proFeatureMessage = __('Conditional logic is a Pro feature. Upgrade to create advanced conditions for your workflows.', 'post-expirator'),
    onQueryChange = null,
    isProFeature = false,
    defaultField = '',
    queryFields = null
}) => {
    // Return the actual component
    return ({ name, label, defaultValue, onChange, variables }) => {
        const { query, setQuery, formatCondition, operators } = useConditionalLogic({
            defaultValue,
            name,
            onChange,
            variables: queryFields ?? variables,
            onQueryChange
        });

        const {
            isModalOpen,
            onCloseModal,
            openModal
        } = useModalManagement({onChange, name, formatCondition});

        const [ editorRef ] = useEditorSetup();
        const [ convertLegacyVariables ] = useLegacyVariables();

        const { setCurrentConditionalQuery } = useDispatch(editorStore);

        const getDefaultField = useCallback((field) => {
            return defaultField;
        }, []);

        useEffect(() => {
            convertLegacyVariables(query);
        }, []);

        useEffect(() => {
            setCurrentConditionalQuery(query);
        }, [query]);

        const queryBuilderControlElements = {
            fieldSelector: FieldComponent,
            valueEditor: ValueComponent,
            notToggle: NotToggle,
            addRuleAction: AddElementButton,
            addGroupAction: AddElementButton,
            removeGroupAction: RemoveElementButton,
            removeRuleAction: RemoveElementButton,
            cloneRuleAction: DuplicateRuleButton,
            cloneGroupAction: DuplicateRuleButton,
            combinatorSelector: CombinatorSelector,
            operatorSelector: OperatorSelector,
        };

        const queryBuilderContext = useMemo(() => ({
            variables: variables,
            name: name,
            label: label,
        }), [variables, name, label]);

        const formattedCondition = useMemo(() => formatCondition(), [formatCondition]);

        const isPro = useIsPro();

        return (
            <div className='conditional-editor'>
                <Button onClick={openModal} variant="secondary">
                    {buttonText}
                </Button>

                <ConditionPreview
                    defaultValue={formattedCondition}
                    editorRef={editorRef}
                    editorProps={EDITOR_PROPS}
                    editorOptions={EDITOR_OPTIONS}
                    naturalLanguage={formattedCondition.natural}
                />

                {! isPro && isProFeature && (
                    <div className="conditional-editor-pro-feature-message">
                        <p className="description margin-top">
                            {proFeatureMessage}
                        </p>
                    </div>
                )}

                {isModalOpen && (
                    <Modal
                        onClose={onCloseModal}
                        title={modalTitle}
                        onRequestClose={onCloseModal}
                        className="workflow-editor-modal conditional-editor-modal"
                    >
                        <p>
                            {modalDescription}
                        </p>
                        <QueryBuilderDnD>
                            <QueryBuilder
                                fields={variables}
                                onQueryChange={setQuery}
                                query={query}
                                addRuleToNewGroups
                                parseNumbers="strict-limited"
                                showCombinatorsBetweenRules
                                showNotToggle
                                enableDragAndDrop={true}
                                showCloneButtons={true}
                                controlClassnames={QUERY_BUILDER_CONTROL_CLASSNAMES}
                                translations={QUERY_BUILDER_TRANSLATIONS}
                                controlElements={queryBuilderControlElements}
                                context={queryBuilderContext}
                                getDefaultField={getDefaultField}
                                operators={operators}
                            />
                        </QueryBuilderDnD>
                        <ModalFooter onClose={ onCloseModal } />
                    </Modal>
                )}
            </div>
        );
    };
};
