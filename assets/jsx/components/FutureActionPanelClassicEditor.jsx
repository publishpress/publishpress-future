import { FutureActionPanel } from "./";
import { select, useSelect } from "@wordpress/data";
import { useEffect } from "@wordpress/element";

export const FutureActionPanelClassicEditor = (props) => {
    const browserTimezoneOffset = new Date().getTimezoneOffset();

    const getElementByName = (name) => {
        return document.getElementsByName(name)[0];
    }

    const onChangeData = (attribute, value) => {
        const store = select(props.storeName);

        getElementByName('future_action_enabled').value = store.getEnabled() ? 1 : 0;
        getElementByName('future_action_action').value = store.getAction();
        getElementByName('future_action_new_status').value = store.getNewStatus();
        getElementByName('future_action_date').value = store.getDate();
        getElementByName('future_action_terms').value = store.getTerms().join(',');
        getElementByName('future_action_taxonomy').value = store.getTaxonomy();
    }

    const getTermsFromElementByName = (name) => {
        const element = getElementByName(name);
        if (!element) {
            return [];
        }

        let terms = element.value.split(',');

        if (terms.length === 1 && terms[0] === '') {
            terms = [];
        }

        return terms.map(term => parseInt(term));
    }

    const getElementValueByName = (name) => {
        const element = getElementByName(name);
        if (!element) {
            return '';
        }

        return element.value;
    }

    const data = {
        enabled: getElementValueByName('future_action_enabled') === '1',
        action: getElementValueByName('future_action_action'),
        newStatus: getElementValueByName('future_action_new_status'),
        date: getElementValueByName('future_action_date'),
        terms: getTermsFromElementByName('future_action_terms'),
        taxonomy: getElementValueByName('future_action_taxonomy'),
    };

    const onDataIsValid = () => {
        jQuery('#publish').prop('disabled', false);
    }

    const onDataIsInvalid = () => {
        jQuery('#publish').prop('disabled', true);
    }

    return (
        <div className={'post-expirator-panel'}>
            <FutureActionPanel
                context={'classic-editor'}
                postType={props.postType}
                isCleanNewPost={props.isNewPost}
                actionsSelectOptions={props.actionsSelectOptions}
                statusesSelectOptions={props.statusesSelectOptions}
                enabled={data.enabled}
                calendarIsVisible={true}
                action={data.action}
                newStatus={data.newStatus}
                date={data.date}
                terms={data.terms}
                taxonomy={data.taxonomy}
                taxonomyName={props.taxonomyName}
                onChangeData={onChangeData}
                is12Hour={props.is12Hour}
                timeFormat={props.timeFormat}
                startOfWeek={props.startOfWeek}
                storeName={props.storeName}
                strings={props.strings}
                onDataIsValid={onDataIsValid}
                hideCalendarByDefault={props.hideCalendarByDefault}
                hiddenFields={props.hiddenFields}
                wpTimezone={props.wpTimezone}
                showTitle={false}
                onDataIsInvalid={onDataIsInvalid} />
        </div>
    );
};
