import { FutureActionPanel } from './';
import { useSelect, select } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

export const FutureActionPanelQuickEdit = (props) => {
    const onChangeData = (attribute, value) => {};

    const date = useSelect((select) => select(props.storeName).getDate(), []);
    const enabled = useSelect((select) => select(props.storeName).getEnabled(), []);
    const action = useSelect((select) => select(props.storeName).getAction(), []);
    const terms = useSelect((select) => select(props.storeName).getTerms(), []);
    const taxonomy = useSelect((select) => select(props.storeName).getTaxonomy(), []);
    const hasValidData = useSelect((select) => select(props.storeName).getHasValidData(), []);
    const newStatus = useSelect((select) => select(props.storeName).getNewStatus(), []);

    let termsString = terms;
    if (typeof terms === 'object') {
        termsString = terms.join(',');
    }

    const onDataIsValid = () => {
        jQuery('.button-primary.save').prop('disabled', false);
    }

    const onDataIsInvalid = () => {
        jQuery('.button-primary.save').prop('disabled', true);
    }

    return (
        <div className={'post-expirator-panel'}>
            <FutureActionPanel
                context={'quick-edit'}
                postType={props.postType}
                isCleanNewPost={props.isNewPost}
                actionsSelectOptions={props.actionsSelectOptions}
                statusesSelectOptions={props.statusesSelectOptions}
                enabled={enabled}
                calendarIsVisible={false}
                action={action}
                newStatus={newStatus}
                date={date}
                terms={terms}
                taxonomy={taxonomy}
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
                showTitle={true}
                onDataIsInvalid={onDataIsInvalid} />

            {/* Quick edit JS code will save only fields with name inside the edit row */}
            <input type="hidden" name={'future_action_enabled'} value={enabled ? 1 : 0} />
            <input type="hidden" name={'future_action_action'} value={action ? action : ''} />
            <input type="hidden" name={'future_action_new_status'} value={newStatus ? newStatus : ''} />
            <input type="hidden" name={'future_action_date'} value={date ? date : ''} />
            <input type="hidden" name={'future_action_terms'} value={termsString ? termsString : ''} />
            <input type="hidden" name={'future_action_taxonomy'} value={taxonomy ? taxonomy : ''} />
            <input type="hidden" name={'future_action_view'} value="quick-edit" />
            <input type="hidden" name={'_future_action_nonce'} value={props.nonce} />
        </div>
    );
};
