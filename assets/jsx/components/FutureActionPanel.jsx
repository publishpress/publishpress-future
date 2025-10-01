import { compact } from '../utils';
import { ToggleCalendarDatePicker } from './ToggleCalendarDatePicker';
import { PluginArea } from '@wordpress/plugins';
import { Fill, Slot, SlotFillProvider } from '@wordpress/components';
import { FutureActionPanelAfterActionField } from './FutureActionPanelAfterActionField';
import { FutureActionPanelTop } from './FutureActionPanelTop';

const { PanelRow, CheckboxControl, SelectControl, FormTokenField, Spinner, BaseControl } = wp.components;
const { Fragment, useEffect, useState } = wp.element;
const { decodeEntities } = wp.htmlEntities;
const { addQueryArgs } = wp.url;
const {
    useSelect,
    useDispatch
} = wp.data;
const { apiFetch } = wp;

export const FutureActionPanel = (props) => {
    const {
        action,
        date,
        enabled,
        terms,
        taxonomy,
        taxonomyName,
        termsListByName,
        termsListById,
        isFetchingTerms,
        calendarIsVisible,
        hasValidData,
        newStatus,
    } = useSelect((select) => {
        return {
            action: select(props.storeName).getAction(),
            date: select(props.storeName).getDate(),
            enabled: select(props.storeName).getEnabled(),
            terms: select(props.storeName).getTerms(),
            taxonomy: select(props.storeName).getTaxonomy(),
            taxonomyName: select(props.storeName).getTaxonomyName(),
            termsListByName: select(props.storeName).getTermsListByName(),
            termsListById: select(props.storeName).getTermsListById(),
            isFetchingTerms: select(props.storeName).getIsFetchingTerms(),
            calendarIsVisible: select(props.storeName).getCalendarIsVisible(),
            hasValidData: select(props.storeName).getHasValidData(),
            newStatus: select(props.storeName).getNewStatus(),
        };
    });

    const extraData = useSelect((select) => {
        return select(props.storeName).getExtraData();
    }, [props.storeName]);

    useEffect(() => {
        if (props.context === 'block-editor' && props.onChangeData) {
            props.onChangeData('extraData', extraData);
        }
    }, [extraData, props.context, props.onChangeData]);

    const hiddenFields = props.hiddenFields || {};

    const [validationError, setValidationError] = useState('');

    const {
        setAction,
        setDate,
        setEnabled,
        setTerms,
        setTaxonomy,
        setTermsListByName,
        setTermsListById,
        setTaxonomyName,
        setIsFetchingTerms,
        setCalendarIsVisible,
        setHasValidData,
        setNewStatus
    } = useDispatch(props.storeName);

    const mapTermsListById = (terms) => {
        if (typeof terms !== 'object' || terms === null) {
            return {};
        }

        return terms.map((term) => {
            return termsListById[term];
        });
    }

    const insertTerm = (term) => {
        termsListByName[term] = { id: term, count: 0, description: "", link: "", name: term, slug: term, taxonomy: taxonomy };
        termsListById[term] = term;
        setTermsListByName(termsListByName);
        setTermsListById(termsListById);
        setTerms([...terms, term]);

    }

    const mapTermsListByName = (terms) => {
        if (typeof terms !== 'object' || terms === null) {
            return {};
        }

        return terms.map((term) => {
            if (termsListByName[term]) {
                return termsListByName[term].id;
            }

            insertTerm(term);

            return term;
        });
    }

    const callOnChangeData = (attribute, value) => {
        if (typeof props.onChangeData === 'function') {
            props.onChangeData(attribute, value);
        }
    }

    const handleEnabledChange = (isChecked) => {
        setEnabled(isChecked);

        if (isChecked) {
            setAction(props.action);
            setDate(props.date);
            setNewStatus(props.newStatus);
            setTerms(props.terms);
            setTaxonomy(props.taxonomy);

            fetchTerms();
        }

        callOnChangeData('enabled', isChecked);
    }

    const handleActionChange = (value) => {
        setAction(value);

        callOnChangeData('action', value);
    }

    const handleNewStatusChange = (value) => {
        setNewStatus(value);

        callOnChangeData('newStatus', value);
    }

    const handleDateChange = (value) => {
        setDate(value);

        callOnChangeData('date', value);
    }

    const handleTermsChange = (value) => {
        value = mapTermsListByName(value);

        setTerms(value);

        callOnChangeData('terms', value);
    }

    const fetchTerms = () => {
        let termsListByName = {};
        let termsListById = {};

        if (!taxonomy) {
            return;
        }

        setIsFetchingTerms(true);

        apiFetch({
            path: addQueryArgs(`publishpress-future/v1/terms/${taxonomy}`),
        }).then((result) => {
            result.terms.forEach(term => {
                termsListByName[decodeEntities(term.name)] = term;
                termsListById[term.id] = decodeEntities(term.name);
            });

            setTermsListByName(termsListByName);
            setTermsListById(termsListById);
            setTaxonomyName(decodeEntities(result.taxonomyName));
            setIsFetchingTerms(false);
        });
    }

    const storeCalendarIsVisibleOnStorage = (value) => {
        localStorage.setItem('FUTURE_ACTION_CALENDAR_IS_VISIBLE_' + props.context, value ? '1' : '0');
    }

    const getCalendarIsVisibleFromStorage = () => {
        return localStorage.getItem('FUTURE_ACTION_CALENDAR_IS_VISIBLE_' + props.context);
    }

    useEffect(() => {
        if (props.autoEnableAndHideCheckbox) {
            setEnabled(true);
        } else {
            setEnabled(props.enabled);
        }

        setAction(props.action);
        setNewStatus(props.newStatus);
        setDate(props.date);
        setTerms(props.terms);
        setTaxonomy(props.taxonomy);

        if (getCalendarIsVisibleFromStorage() === null) {
            setCalendarIsVisible(props.calendarIsVisible);
        } else {
            setCalendarIsVisible(getCalendarIsVisibleFromStorage() === '1' && ! props.hideCalendarByDefault);
        }

        // We need to get the value directly from the props because the value from the store is not updated yet
        if (props.enabled) {
            if (props.isCleanNewPost) {
                // Force populate the default values
                handleEnabledChange(true);
            }

            fetchTerms();
        }
    }, []);

    useEffect(() => {
        storeCalendarIsVisibleOnStorage(calendarIsVisible);
    }, [calendarIsVisible]);

    useEffect(() => {
        if (hasValidData && props.onDataIsValid) {
            props.onDataIsValid();
        }

        if (!hasValidData && props.onDataIsInvalid) {
            props.onDataIsInvalid();
        }
    }, [hasValidData]);

    let selectedTerms = [];
    if (terms && terms.length > 0 && termsListById) {
        selectedTerms = compact(mapTermsListById(terms));

        if (typeof selectedTerms === 'string') {
            selectedTerms = [];
        }
    }

    let termsListByNameKeys = [];
    if (typeof termsListByName === 'object' && termsListByName !== null) {
        termsListByNameKeys = Object.keys(termsListByName);
    }

    const panelClass = calendarIsVisible ? 'future-action-panel' : 'future-action-panel hidden-calendar';
    const contentPanelClass = calendarIsVisible ? 'future-action-panel-content' : 'future-action-panel-content hidden-calendar';
    const datePanelClass = calendarIsVisible ? 'future-action-date-panel' : 'future-action-date-panel hidden-calendar';

    let is24hour;
    if (props.timeFormat === 'inherited') {
        is24hour = !props.is12Hour;
    } else {
        is24hour = props.timeFormat === '24h';
    }

    const replaceCurlyBracketsWithLink = (string, href, target) => {
        const parts = string.split('{');
        const result = [];

        result.push(parts.shift());

        for (const part of parts) {
            const [before, after] = part.split('}');

            result.push(
                <a href={href} target={target} key={href}>{before}</a>
            );

            result.push(after);
        }

        return result;
    };

    // Remove items from actions list if related to taxonomies and there is no taxonmoy for the post type
    let actionsSelectOptions = props.actionsSelectOptions;
    if (!props.taxonomy) {
        actionsSelectOptions = props.actionsSelectOptions.filter((item) => {
            return ['category', 'category-add', 'category-remove', 'category-remove-all'].indexOf(item.value) === -1;
        });
    }

    const HelpText = replaceCurlyBracketsWithLink(props.strings.timezoneSettingsHelp, '/wp-admin/options-general.php#timezone_string', '_blank');
    const displayTaxonomyField = String(action).includes('category') && action !== 'category-remove-all';

    let termsFieldLabel = taxonomyName;
    switch (action) {
        case 'category':
            termsFieldLabel = props.strings.newTerms.replace('%s', taxonomyName);
            break;
        case 'category-remove':
            termsFieldLabel = props.strings.removeTerms.replace('%s', taxonomyName);
            break;
        case 'category-add':
            termsFieldLabel = props.strings.addTerms.replace('%s', taxonomyName);
            break;
    }

    const validateData = () => {
        let valid = true;

        if (!enabled) {
            setValidationError('');
            return true;
        }

        if (!action) {
            setValidationError(props.strings.errorActionRequired);
            valid = false;
        }

        if (!date) {
            setValidationError(props.strings.errorDateRequired);
            valid = false;
        }

        if (date) {
            const wpTimezone = props.wpTimezone || "UTC";
            const selectedDate = new Date(date);
            const now = new Date();
            let nowInWpTimezone;

            if (wpTimezone.match(/^[+-]\d{2}:\d{2}$/)) {
                const [, sign, hours, minutes] = wpTimezone.match(/^([+-])(\d{2}):(\d{2})$/);
                const offsetMinutes = (parseInt(hours) * 60 + parseInt(minutes)) * (sign === '+' ? 1 : -1);

                // Convert browser time to WordPress timezone
                // Browser offset from UTC
                const browserOffsetMinutes = now.getTimezoneOffset();

                // Adjust from browser timezone to WordPress timezone
                const totalOffsetMinutes = offsetMinutes + browserOffsetMinutes;

                nowInWpTimezone = new Date(now.getTime() + (totalOffsetMinutes * 60000));
            } else {
                try {
                    nowInWpTimezone = new Date(now.toLocaleString("en-US", { timeZone: wpTimezone }));
                } catch (error) {
                    nowInWpTimezone = new Date(now.toLocaleString("en-US", { timeZone: "UTC" }));
                }
            }

            if (selectedDate < nowInWpTimezone) {
                setValidationError(props.strings.errorDateInPast);
                valid = false;
            }
        }

        const isTermRequired = ['category', 'category-add', 'category-remove'].includes(action);
        const noTermIsSelected = terms.length === 0 || (terms.length === 1 && (terms[0] === '' || terms[0] === '0'));

        if (isTermRequired && noTermIsSelected) {
            setValidationError(props.strings.errorTermsRequired);
            valid = false;
        }

        if (valid) {
            setValidationError('');
        }

        return valid;
    }

    useEffect(() => {
        if (!enabled) {
            setHasValidData(true);
            setValidationError('');

            return;
        }

        setHasValidData(validateData());
    }, [action, date, enabled, terms, taxonomy]);

    // This adds a 'cancel' class to the input when the user clicks on the
    // field to prevent the form from being submitted. This is a workaround
    // for the issue on the quick-edit form where the form is submitted when
    // the user presses the 'Enter' key trying to add a term to the field.
    const forceIgnoreAutoSubmitOnEnter = (e) => {
        jQuery(e.target).addClass('cancel');
    }

    return (
        <SlotFillProvider>
            <div className={panelClass}>
                {props.autoEnableAndHideCheckbox && (
                    <input type="hidden" name={'future_action_enabled'} value={1} />
                )}

                {props.showTitle && (
                    <div style={{ fontWeight: 'bold', marginBottom: '10px' }}>{props.strings.panelTitle}</div>
                )}

                <FutureActionPanelTop.Slot fillProps={{ storeName: props.storeName }} />

                {!props.autoEnableAndHideCheckbox && (
                    <PanelRow>
                        <CheckboxControl
                            label={props.strings.enablePostExpiration}
                            checked={enabled || false}
                            onChange={handleEnabledChange}
                            className="future-action-enable-checkbox"
                        />
                    </PanelRow>
                )}

                {enabled && (
                    <Fragment>
                        {!hiddenFields['_expiration-date-type'] && (
                            <PanelRow className={contentPanelClass + ' future-action-full-width'}>
                                <SelectControl
                                    label={props.strings.action}
                                    value={action}
                                    options={actionsSelectOptions}
                                    onChange={handleActionChange}
                                    className="future-action-select-action"
                                />
                            </PanelRow>
                        )}

                        <FutureActionPanelAfterActionField.Slot fillProps={{ storeName: props.storeName }} />

                        {!hiddenFields['_expiration-date-post-status'] && action === 'change-status' &&
                            <PanelRow className="new-status">
                                <SelectControl
                                    label={props.strings.newStatus}
                                    options={props.statusesSelectOptions}
                                    value={newStatus}
                                    onChange={handleNewStatusChange}
                                    className="future-action-select-new-status"
                                />
                            </PanelRow>
                        }

                        {
                            !hiddenFields['_expiration-date-taxonomy'] && displayTaxonomyField && (
                                isFetchingTerms && (
                                    <PanelRow>
                                        <BaseControl label={taxonomyName}>
                                            {`${props.strings.loading} (${taxonomyName})`}
                                            <Spinner />
                                        </BaseControl>
                                    </PanelRow>
                                )
                                || (!taxonomy && (
                                    <PanelRow>
                                        <BaseControl label={taxonomyName} className="future-action-warning">
                                            <div>
                                                <i className="dashicons dashicons-warning"></i> {props.strings.noTaxonomyFound}
                                            </div>
                                        </BaseControl>
                                    </PanelRow>
                                )
                                    || (
                                        termsListByNameKeys.length === 0 && (
                                            <PanelRow>
                                                <BaseControl label={taxonomyName} className="future-action-warning">
                                                    <div>
                                                        <i className="dashicons dashicons-warning"></i> {props.strings.noTermsFound}
                                                    </div>
                                                </BaseControl>
                                            </PanelRow>
                                        )
                                        || (
                                            <PanelRow className="future-action-full-width">
                                                <BaseControl>
                                                    <FormTokenField
                                                        label={termsFieldLabel}
                                                        value={selectedTerms}
                                                        suggestions={termsListByNameKeys}
                                                        onChange={handleTermsChange}
                                                        placeholder={props.strings.addTermsPlaceholder}
                                                        className="future-action-terms"
                                                        maxSuggestions={1000}
                                                        onFocus={forceIgnoreAutoSubmitOnEnter}
                                                        __experimentalExpandOnFocus={true}
                                                        __experimentalAutoSelectFirstMatch={true}
                                                    />
                                                </BaseControl>
                                            </PanelRow>
                                        )
                                    )
                                )
                            )
                        }

                        {!hiddenFields['_expiration-date'] && (
                            <>
                                <PanelRow className={datePanelClass}>
                                    <ToggleCalendarDatePicker
                                        currentDate={date}
                                        onChangeDate={handleDateChange}
                                        onToggleCalendar={() => setCalendarIsVisible(!calendarIsVisible)}
                                        is12Hour={!is24hour}
                                        startOfWeek={props.startOfWeek}
                                        isExpanded={calendarIsVisible}
                                        strings={props.strings}
                                    />
                                </PanelRow>

                                <PanelRow>
                                    <div className="future-action-help-text">
                                        <hr />

                                        <span className="dashicons dashicons-info"></span> {HelpText}
                                    </div>
                                </PanelRow>
                            </>
                        )}

                        {!hasValidData && (
                            <PanelRow>
                                <BaseControl className="notice notice-error">
                                    <div>{validationError}</div>
                                </BaseControl>
                            </PanelRow>
                        )}
                    </Fragment>
                )}
            </div>
            <PluginArea scope='publishpress-future' />
        </SlotFillProvider>
    );
};
