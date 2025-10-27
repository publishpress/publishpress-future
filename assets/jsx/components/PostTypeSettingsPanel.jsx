/*
 * Copyright (c) 2025, Ramble Ventures
 */

import {
    SettingRow,
    SettingsTable,
    SelectControl,
    TextControl,
    TokensControl,
    CheckboxControl
} from './';
import { useEffect, useState } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { applyFilters } from '@wordpress/hooks';
import DateOffsetPreview from './DateOffsetPreview';
import { Tooltip } from "@wordpress/components";

const { apiFetch } = wp;

const { PanelRow } = wp.components;

export const PostTypeSettingsPanel = function (props) {
    const originalExpireTypeList = props.expireTypeList[props.postType];

    const [postTypeTaxonomy, setPostTypeTaxonomy] = useState(props.settings.taxonomy);
    const [termOptions, setTermOptions] = useState([]);
    const [termsSelectIsLoading, setTermsSelectIsLoading] = useState(false);
    const [selectedTerms, setSelectedTerms] = useState([]);
    const [settingHowToExpire, setSettingHowToExpire] = useState(props.settings.howToExpire);
    const [isActive, setIsActive] = useState(props.settings.active);
    const [expireOffset, setExpireOffset] = useState(props.settings.defaultExpireOffset);
    const [emailNotification, setEmailNotification] = useState(props.settings.emailNotification);
    const [isAutoEnabled, setIsAutoEnabled] = useState(props.settings.autoEnabled);
    const [hasValidData, setHasValidData] = useState(true);
    const [validationError, setValidationError] = useState('');
    const [taxonomyLabel, setTaxonomyLabel] = useState('');
    const [howToExpireList, setHowToExpireList] = useState(originalExpireTypeList);
    const [newStatus, setNewStatus] = useState(props.settings.newStatus);
    const [hasPendingValidation, setHasPendingValidation] = useState(false);

    const offset = expireOffset ? expireOffset : props.settings.globalDefaultExpireOffset;

    const isPro = props.isPro != "" && props.isPro === "1";

    const HelpText = (props) => {
        return (
            <p className="description">{props.children}</p>
        );
    };

    const FieldRow = (props) => {
        let className = 'publishpress-settings-field-row';

        if (props.className) {
            className += ' ' + props.className;
        }

        return (
            <div className={className}>{props.children}</div>
        );
    };

    const taxonomyRelatedActions = [
        'category',
        'category-add',
        'category-remove',
        'category-remove-all'
    ];

    const onChangeTaxonomy = function (value) {
        setPostTypeTaxonomy(value);
    };

    const onChangeTerms = (value) => {
        setSelectedTerms(value);
    };

    const onChangeHowToExpire = (value) => {
        setSettingHowToExpire(value);
    }

    const onChangeActive = (value) => {
        setIsActive(value);
    }

    const onChangeExpireOffset = (value) => {
        setExpireOffset(value);
    }

    const onChangeEmailNotification = (value) => {
        setEmailNotification(value);
    }

    const onChangeAutoEnabled = (value) => {
        setIsAutoEnabled(value);
    }

    useEffect(() => {
        // Remove items from expireTypeList if related to taxonomies and there is no taxonmoy for the post type
        if (props.taxonomiesList.length === 0) {
            let newExpireTypeList = [];

            newExpireTypeList = howToExpireList.filter((item) => {
                return taxonomyRelatedActions.indexOf(item.value) === -1;
            });

            setHowToExpireList(newExpireTypeList);
        }
    }, []);

    useEffect(() => {
        if (!postTypeTaxonomy || !props.taxonomiesList) {
            return;
        }

        setTermsSelectIsLoading(true);
        apiFetch({
            path: addQueryArgs(`publishpress-future/v1/terms/${postTypeTaxonomy}`),
        }).then((result) => {
            let options = [];

            let settingsTermsOptions = null;
            let option;
            if (result.terms && Array.isArray(result.terms)) {
                result.terms.forEach(term => {
                    option = { value: term.id, label: term.name };
                    options.push(option);

                    if (postTypeTaxonomy === props.settings.taxonomy && props.settings.terms.includes(term.id)) {
                        if (settingsTermsOptions === null) {
                            settingsTermsOptions = [];
                        }

                        settingsTermsOptions.push(option.label);
                    }
                });
            }

            setTermOptions(options);
            setSelectedTerms(settingsTermsOptions);
            setTermsSelectIsLoading(false);
        });

        props.taxonomiesList.forEach((taxonomy) => {
            if (taxonomy.value === postTypeTaxonomy) {
                setTaxonomyLabel(taxonomy.label);
            }
        });
    }, [postTypeTaxonomy]);

    useEffect(() => {
        if (!taxonomyLabel) {
            return;
        }

        // Update the list of actions replacing the taxonomy name.
        let newExpireTypeList = [];

        originalExpireTypeList.forEach((expireType) => {
            let label = expireType.label;

            if (taxonomyRelatedActions.indexOf(expireType.value) !== -1) {
                label = label.replace('%s', taxonomyLabel.toLowerCase());
            }

            newExpireTypeList.push({
                value: expireType.value,
                label: label
            });
        });

        setHowToExpireList(newExpireTypeList);
    }, [taxonomyLabel]);

    useEffect(() => {
        if (hasValidData && props.onDataIsValid) {
            props.onDataIsValid(props.postType);
        }

        if (!hasValidData && props.onDataIsInvalid) {
            props.onDataIsInvalid(props.postType);
        }
    }, [hasValidData]);

    useEffect(() => {
        if (hasPendingValidation && props.onValidationStarted) {
            props.onValidationStarted(props.postType);
        }

        if (!hasPendingValidation && props.onValidationFinished) {
            props.onValidationFinished(props.postType);
        }
    }, [hasPendingValidation]);

    const termOptionsLabels = termOptions.map((term) => term.label);

    let settingsRows = [
        <SettingRow label={props.text.fieldActive} key={'expirationdate_activemeta-' + props.postType}>
            <CheckboxControl
                name={'expirationdate_activemeta-' + props.postType}
                checked={isActive || false}
                label={props.text.fieldActiveLabel}
                onChange={onChangeActive}
            />
        </SettingRow>
    ];

    if (isActive) {
        settingsRows.push(
            <SettingRow label={props.text.fieldAutoEnable} key={'expirationdate_autoenable-' + props.postType}>
                <CheckboxControl
                    name={'expirationdate_autoenable-' + props.postType}
                    checked={isAutoEnabled || false}
                    label={props.text.fieldAutoEnableLabel}
                    onChange={onChangeAutoEnabled}
                />
            </SettingRow>
        );

        if (isAutoEnabled) {
            settingsRows.push(
                <SettingRow label={props.text.fieldTaxonomy} key={'expirationdate_taxonomy-' + props.postType}>
                    <SelectControl
                        name={'expirationdate_taxonomy-' + props.postType}
                        options={props.taxonomiesList}
                        selected={postTypeTaxonomy}
                        noItemFoundMessage={props.text.noItemsfound}
                        description={props.text.fieldTaxonomyDescription}
                        data={props.postType}
                        onChange={onChangeTaxonomy}
                    >
                    </SelectControl>
                </SettingRow>
            );

            settingsRows.push(
                <SettingRow label={props.text.fieldHowToExpire} key={'expirationdate_expiretype-' + props.postType}>
                    <SelectControl
                        name={'expirationdate_expiretype-' + props.postType}
                        className={'pe-howtoexpire'}
                        options={howToExpireList}
                        description={props.text.fieldHowToExpireDescription}
                        selected={settingHowToExpire}
                        onChange={onChangeHowToExpire}
                    />

                    {settingHowToExpire === 'change-status' &&
                        <SelectControl
                            name={'expirationdate_newstatus-' + props.postType}
                            options={props.statusesList}
                            selected={newStatus}
                            onChange={setNewStatus}
                        />
                    }

                    {(props.taxonomiesList.length > 0 && (['category', 'category-add', 'category-remove'].indexOf(settingHowToExpire) > -1)) &&
                        <TokensControl
                            label={props.text.fieldTerm}
                            name={'expirationdate_terms-' + props.postType}
                            options={termOptionsLabels}
                            value={selectedTerms}
                            isLoading={termsSelectIsLoading}
                            onChange={onChangeTerms}
                            description={props.text.fieldTermDescription}
                            maxSuggestions={1000}
                            expandOnFocus={true}
                            autoSelectFirstMatch={true}
                        />
                    }
                </SettingRow>
            );

            settingsRows.push(
                <SettingRow label={props.text.fieldDefaultDateTimeOffset} key={'expired-custom-date-' + props.postType}>
                    <TextControl
                        name={'expired-custom-date-' + props.postType}
                        value={expireOffset}
                        loading={hasPendingValidation}
                        placeholder={props.settings.globalDefaultExpireOffset}
                        description={props.text.fieldDefaultDateTimeOffsetDescription}
                        unescapedDescription={true}
                        onChange={onChangeExpireOffset}
                    />

                    <DateOffsetPreview
                        offset={offset}
                        label={props.text.datePreview}
                        labelDatePreview={props.text.datePreviewCurrent}
                        labelOffsetPreview={props.text.datePreviewComputed}
                        setValidationErrorCallback={setValidationError}
                        setHasPendingValidationCallback={setHasPendingValidation}
                        setHasValidDataCallback={setHasValidData}
                    />
                </SettingRow>
            );

            settingsRows.push(
                <SettingRow label={props.text.fieldWhoToNotify} key={'expirationdate_emailnotification-' + props.postType}>
                    <TextControl
                        name={'expirationdate_emailnotification-' + props.postType}
                        className="large-text"
                        value={emailNotification}
                        description={props.text.fieldWhoToNotifyDescription}
                        onChange={onChangeEmailNotification}
                    />
                </SettingRow>
            );
        }

        // Add promotional fields for non-pro users
        if (!isPro) {
            // Custom statuses promotional field
            settingsRows.push(
                <SettingRow label={props.text.fieldCustomStatuses} key={'custom-statuses_promo'}>
                    <FieldRow>
                        <div>
                            <input type="checkbox" disabled />
                            {props.text.fieldCustomStatusesLabel}
                            <Tooltip text={props.text.proFeatureTooltip}>
                                <span className="dashicons dashicons-lock pp-pro-loc-icon"></span>
                            </Tooltip>
                        <HelpText>{props.text.fieldCustomStatusesDescription}</HelpText>
                        </div>
                    </FieldRow>
                </SettingRow>
            );

            // Metadata scheduling promotional field
            settingsRows.push(
                <SettingRow label={props.text.fieldMetadataScheduling} key={'metadata_mapping_promo'}>
                    <FieldRow>
                        <div>
                            <input type="checkbox" disabled />
                            {props.text.fieldMetadataSchedulingLabel}
                            <Tooltip text={props.text.proFeatureTooltip}>
                                <span className="dashicons dashicons-lock pp-pro-loc-icon"></span>
                            </Tooltip>
                        </div>
                        <HelpText>{props.text.fieldMetadataSchedulingDescription}</HelpText>
                    </FieldRow>
                </SettingRow>
            );
        }
    }

    settingsRows = applyFilters('expirationdate_settings_posttype', settingsRows, props, isActive, useState);

    const fieldSetClassNames = props.isVisible ? 'pe-settings-fieldset' : 'pe-settings-fieldset hidden';

    return (
        <div className={fieldSetClassNames}>
            <h2>{props.postTypeLabel}</h2>
            <SettingsTable bodyChildren={settingsRows} />

            {! hasValidData && (
                <PanelRow>
                    <div className="publishpress-future-notice publishpress-future-notice-error">
                        <strong>{props.text.error}:</strong> {validationError}
                    </div>
                </PanelRow>
            )}
        </div>
    );
}
