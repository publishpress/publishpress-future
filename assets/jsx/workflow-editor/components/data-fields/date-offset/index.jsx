import { sprintf, __ } from "@wordpress/i18n";
import {
    TreeSelect,
    DateTimePicker,
    TextControl,
    PanelRow,
    Popover,
    Button,
    __experimentalVStack as VStack,
} from "@wordpress/components";
import { VariablesTreeSelect } from "../../variables-tree-select";
import { DateOffsetPreview } from "../../../../components/DateOffsetPreview";
import { stripTags } from "../../../../utils";
import { useMemo, useState, useCallback } from "@wordpress/element";
import { ExpressionBuilder } from "../expression-builder";

export const DateOffset = ({ name, label, defaultValue, onChange, variables = [], settings }) => {
    const allVariables = useMemo(() => {
        return variables;
    }, [variables]);

    const [isHelpVisible, setIsHelpVisible] = useState(false);
    const [isPreviewValid, setIsPreviewValid] = useState(true);
    const [previewMessage, setPreviewMessage] = useState('');

    const toggleHelp = () => setIsHelpVisible((state) => !state);
    const hideHelp = () => setIsHelpVisible(false);

    const onHasValidationError = (errorMessage) => {
        if (errorMessage) {
            setPreviewMessage(errorMessage);
            setIsPreviewValid(false);
        } else {
            setPreviewMessage('');
            setIsPreviewValid(true);
        }
    }

    const onValidationStarted = () => {
    }

    const onValidationFinished = (isValid) => {
        setIsPreviewValid(isValid);

        if (isValid) {
            setPreviewMessage('');
        }
    }

    const onChangeSetting = useCallback(({ settingName, value }) => {
        const newValue = { ...defaultValue };
        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }, [defaultValue, name, onChange]);

    const dateSourceOptions = useMemo(() => {
        let dateSourceOptions = [
            { name: __("Selected in the calendar", "post-expirator"), id: "calendar" },
            { name: __("When the trigger is activated", "post-expirator"), id: "event"},
            { name: __("When the step is activated", "post-expirator"), id: "step"},
            { name: __("Custom date source", "post-expirator"), id: "custom"},
        ]

        // Filter out hidden date sources
        if (settings && settings?.hideDateSources) {
            dateSourceOptions = dateSourceOptions.filter((option) => {
                return !settings.hideDateSources.includes(option.id);
            });
        }


        return dateSourceOptions;
    }, [settings]);

    const dateStrategyOptions = useMemo(() => {
        let dateSelectionOptions = [
            { name: __("As soon as possible", "post-expirator"), id: "now" },
            { name: __("On a specific date", "post-expirator"), id: "date" },
            { name: __("Relative to a specific date", "post-expirator"), id: "offset" },
        ];

        if (settings && settings?.showEmptyDateOption) {
            const emptyDateOptionLabel = settings?.emptyDateOptionLabel || __("Unchanged", "post-expirator");

            dateSelectionOptions = [
                { name: emptyDateOptionLabel, id: "" },
                ...dateSelectionOptions,
            ]
        }

        if (settings && settings?.hideDateStrategy) {
            dateSelectionOptions = dateSelectionOptions.filter((option) => {
                return !settings.hideDateStrategy.includes(option.id);
            });
        }

        return dateSelectionOptions;
    }, [settings]);

    return (
        <>
            <VStack>
                <PanelRow>
                    <TreeSelect
                        label={label}
                        tree={dateStrategyOptions}
                        selectedId={defaultValue.dateStrategy}
                        onChange={(value) => onChangeSetting({ settingName: "dateStrategy", value })}
                    />
                </PanelRow>

                {(defaultValue.dateStrategy === 'date' || defaultValue.dateStrategy === 'offset') && (
                    <>
                        <PanelRow>
                            <VariablesTreeSelect
                                label={__("Date source", "post-expirator")}
                                tree={dateSourceOptions}
                                selectedId={defaultValue.dateSource}
                                onChange={(value) => onChangeSetting({ settingName: "dateSource", value })}
                            />
                        </PanelRow>

                        {defaultValue.dateSource === 'calendar' && (
                            <PanelRow>
                                <DateTimePicker
                                    currentDate={defaultValue.specificDate}
                                    onChange={(value) => onChangeSetting({ settingName: "specificDate", value })}
                                />
                            </PanelRow>
                        )}

                        {defaultValue.dateSource === 'custom' && (
                            <PanelRow>
                                <ExpressionBuilder
                                    name="customDateSource"
                                    label={__("Custom date source", "post-expirator")}
                                    defaultValue={defaultValue.customDateSource}
                                    onChange={(settingName, value) => {
                                        onChangeSetting({ settingName: 'customDateSource', value });
                                    }}
                                    variables={allVariables}
                                    singleVariableOnly={false}
                                    readOnlyPreview={true}
                                    description={__("Click the button to choose a custom date source from variables that can provide a date.", "post-expirator")}
                                    wrapOnPreview={false}
                                    wrapOnEditor={false}
                                    oneLinePreview={true}
                                />
                            </PanelRow>
                        )}

                        {defaultValue.dateStrategy === 'offset' && (
                            <>
                                <PanelRow>
                                    <TextControl
                                        label={__("Offset", "post-expirator")}
                                        value={defaultValue.dateOffset}
                                        onChange={(value) => onChangeSetting({ settingName: "dateOffset", value })}
                                    />
                                </PanelRow>

                                <PanelRow>
                                    <DateOffsetPreview
                                        offset={defaultValue.dateOffset}
                                        label={__("Date Preview", "post-expirator")}
                                        labelDatePreview={__("Current Date", "post-expirator")}
                                        labelOffsetPreview={__("Computed Date", "post-expirator")}
                                        setValidationErrorCallback={onHasValidationError}
                                        setHasPendingValidationCallback={onValidationStarted}
                                        setHasValidDataCallback={onValidationFinished}
                                        compactView={true}
                                    />
                                </PanelRow>

                                {! isPreviewValid && (
                                    <PanelRow>
                                        <div className="publishpress-future-notice publishpress-future-notice-error">
                                            {__("Error: ", "post-expirator")} {previewMessage}
                                        </div>
                                    </PanelRow>
                                )}

                                <PanelRow>
                                    <Button variant="link" onClick={toggleHelp}>
                                        {__("Click for more information", "post-expirator")}
                                        {isHelpVisible && (
                                            <Popover>
                                                <div className="settings-field-help-popover">
                                                    <Button variant="tertiary" icon={'no-alt'} />

                                                    <div dangerouslySetInnerHTML={{
                                                        __html: sprintf(
                                                            stripTags(
                                                                __("For more information on formatting, see the %sPHP strtotime function%s. For example, you could enter %s+1 month%s or %s+1 week 2 days 4 hours 2 seconds%s or %snext Thursday%s. Please use only phrases in English.", "post-expirator")
                                                            ),
                                                            "<a href='https://www.php.net/manual/en/function.strtotime.php' target='_blank'>",
                                                            "</a>",
                                                            "<code>",
                                                            "</code>",
                                                            "<code>",
                                                            "</code>",
                                                            "<code>",
                                                            "</code>",
                                                        )
                                                    }} />
                                                </div>
                                            </Popover>
                                        )}
                                    </Button>
                                </PanelRow>
                            </>
                        )}
                    </>
                )}
            </VStack>
        </>
    )
};

export default DateOffset;
