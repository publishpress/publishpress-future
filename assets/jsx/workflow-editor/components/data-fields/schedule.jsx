import { sprintf, __ } from "@wordpress/i18n";
import {
    PanelRow,
    Slot,
    __experimentalVStack as VStack,
    ToggleControl,
    __experimentalNumberControl as NumberControl,
} from "@wordpress/components";
import { useMemo } from "@wordpress/element";
import { useSelect } from "@wordpress/data";
import { store as editorStore } from "../editor-store";
import { FEATURE_ADVANCED_SETTINGS } from "../../constants";
import Recurrence from "./recurrence";
import ProFeatureField from "../pro-feature-field";
import ExpressionBuilder from "./expression-builder";
import { DescriptionText } from "./description-text";
import { DateOffset } from "./date-offset";
import { useCallback } from "react";
import { useIsPro } from "../../contexts/pro-context";

/**
 *  When to execute:
 *   - now - As soon as possible after event
 *   - date - At a specific date
 *   - offset - After a specific date
 *
 *   Recurrence:
 *   - single - Non-repeating
 *   - custom - Interval in seconds
 *   - cron_... - Once a minute
 *   - cron_... - Daily
 *
 *   Until:
 *   - Forever
 *   - Until specific date
 *   - For a number of times
 *
 */
export function Schedule({ name, label, defaultValue, onChange, variables = [], settings }) {
    const allVariables = useMemo(() => {
        return variables;
    }, [variables]);

    const defaultSpecificDate = new Date();
    defaultSpecificDate.setDate(defaultSpecificDate.getDate() + 3);

    const defaultRepeatDate = new Date();
    defaultRepeatDate.setDate(defaultRepeatDate.getDate() + 7);

    defaultValue = {
        whenToRun: "now",
        dateSource: "calendar",
        recurrence: "single",
        repeatUntil: "forever",
        repeatInterval: "3600",
        repeatIntervalUnit: "seconds",
        repeatTimes: "5",
        repeatUntilDate: defaultRepeatDate,
        unique: true,
        priority: "10",
        specificDate: defaultSpecificDate,
        dateOffset: "+7 days",
        ...defaultValue
    };

    const {
        isAdvancedSettingsEnabled,
    } = useSelect((select) => {
        return {
            isAdvancedSettingsEnabled: select(editorStore).isFeatureActive(FEATURE_ADVANCED_SETTINGS),
        };
    });


    const validDateSources = ['calendar', 'event', 'step', 'custom'];
    const isLegacyDateSource = !validDateSources.includes(defaultValue.dateSource);

    if (isLegacyDateSource) {
        defaultValue.customDateSource = defaultValue.dateSource;
        defaultValue.dateSource = 'custom';
    }

    const hidePreventDuplicateScheduling = settings?.hidePreventDuplicateScheduling;

    const onChangeSetting = useCallback(({ settingName, value }) => {
        const newValue = { ...defaultValue };

        newValue[settingName] = value;

        if (onChange) {
            onChange(name, newValue);
        }
    }, [defaultValue, name, onChange]);

    const onChangeDateOffset = useCallback((settingName, value) => {
        value.whenToRun = value.dateStrategy;
        delete value.dateStrategy;

        if (onChange) {
            onChange(name, { ...defaultValue, ...value });
        }
    }, [defaultValue, name, onChange]);

    const isPro = useIsPro();

    return (
        <>
            <VStack>
                <DateOffset
                    name={name}
                    label={label}
                    defaultValue={{
                        ...defaultValue,
                        dateStrategy: defaultValue.whenToRun,
                    }}
                    onChange={onChangeDateOffset}
                    variables={variables}
                    settings={{
                        hideDateSources: settings?.hideDateSources
                    }}
                />

                {! isPro && (
                    <>
                        <PanelRow className="margin-bottom-0">
                            <ProFeatureField link="https://publishpress.com/links/future-workflow-inspector">
                                <Recurrence label={__("Repeating Action", "post-expirator")} disabled={true} />
                            </ProFeatureField>
                        </PanelRow>
                        <PanelRow className="margin-top-0">
                            <DescriptionText text={__("Choose how often this task should repeat. Select 'Non-repeating' for a one-time action or set an interval for automatic recurrence.", "post-expirator")} />
                        </PanelRow>
                    </>
                )}

                <Slot name="DateOffsetAfterDateSourceField" fillProps={{
                    onChangeSetting,
                    defaultValue,
                }} />

                {isAdvancedSettingsEnabled && (
                    <>
                        {! hidePreventDuplicateScheduling && (
                            <>
                                <PanelRow>
                                    <ExpressionBuilder
                                        name="uniqueIdExpression"
                                        label={__("Unique Task Identifier", "post-expirator")}
                                        defaultValue={defaultValue.uniqueIdExpression ?? ''}
                                        onChange={(settingName, value) => {
                                            onChangeSetting({ settingName: 'uniqueIdExpression', value: value });
                                        }}
                                        variables={allVariables}
                                        description={__("Define a unique ID to detect and prevent duplicate tasks.", "post-expirator")}
                                        oneLinePreview={true}
                                        wrapOnPreview={false}
                                        wrapOnEditor={false}
                                        helpUrl="https://publishpress.com/docs/schedule-delay/#preventing-duplicate-scheduled-tasks-task-identification-guide"
                                    />
                                </PanelRow>

                            </>
                        )}
                    </>
                )}
            </VStack>
        </>
    );
}

export default Schedule;
