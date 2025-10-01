import { FutureActionPanel } from './';
import './css/block-editor.css';

export const FutureActionPanelBlockEditor = (props) => {
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { useDispatch, select } = wp.data;

    const { editPost } = useDispatch('core/editor');

    const editPostAttribute = (newAttribute) => {
        const attribute = {
            publishpress_future_action: {}
        };

        // For each property on newAttribute, set the value on attribute
        for (const [name, value] of Object.entries(newAttribute)) {
            attribute.publishpress_future_action[name] = value;
        }

        editPost(attribute);
    }

    const onChangeData = (attribute, value) => {
        const store = select(props.storeName);

        const newAttribute = {
            'enabled': store.getEnabled()
        }

        newAttribute['action'] = store.getAction();
        newAttribute['newStatus'] = store.getNewStatus();
        newAttribute['date'] = store.getDate();
        newAttribute['terms'] = store.getTerms();
        newAttribute['taxonomy'] = store.getTaxonomy();
        newAttribute['extraData'] = store.getExtraData();

        editPostAttribute(newAttribute);
    }

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
