import { FutureActionPanel } from './';
import './css/block-editor.css';
import { futureActionAttributesEqual } from '../utils';
import { useCallback } from '@wordpress/element';

export const FutureActionPanelBlockEditor = (props) => {
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { useDispatch, select } = wp.data;

    const { editPost } = useDispatch('core/editor');

    const editPostAttribute = useCallback((newAttribute) => {
        const currentAttribute = select('core/editor').getCurrentPostAttribute('publishpress_future_action');

        if (futureActionAttributesEqual(newAttribute, currentAttribute)) {
            return;
        }

        editPost({
            publishpress_future_action: {
                ...newAttribute,
            },
        });
    }, [editPost]);

    const onChangeData = useCallback(() => {
        const store = select(props.storeName);

        const newAttribute = {
            enabled: store.getEnabled(),
            action: store.getAction(),
            newStatus: store.getNewStatus(),
            date: store.getDate(),
            terms: store.getTerms(),
            taxonomy: store.getTaxonomy(),
            extraData: store.getExtraData(),
        };

        editPostAttribute(newAttribute);
    }, [props.storeName, editPostAttribute]);

    const rawData = select('core/editor').getEditedPostAttribute('publishpress_future_action');
    const data = rawData || {
        enabled: false,
        action: '',
        newStatus: '',
        date: '',
        terms: [],
        taxonomy: '',
        extraData: {}
    };

    const { lockPostSaving, unlockPostSaving } = useDispatch('core/editor');

    const onDataIsValid = () => {
        unlockPostSaving('future-action');
    }

    const onDataIsInvalid = () => {
        lockPostSaving('future-action');
    }

    return (
        <PluginDocumentSettingPanel
            name={'publishpress-future-action-panel'}
            title={props.strings.panelTitle}
            initialOpen={props.postTypeDefaultConfig.autoEnable}
            className={'post-expirator-panel'}>
            <div id='publishpress-future-block-editor'>
                <FutureActionPanel
                    context={'block-editor'}
                    postType={props.postType}
                    isCleanNewPost={props.isCleanNewPost}
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
        </PluginDocumentSettingPanel>
    );
};
