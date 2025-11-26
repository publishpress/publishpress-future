import { PanelBody } from "@wordpress/components";
import { useSelect, useDispatch } from "@wordpress/data";
import { store as editorStore } from "../editor-store";

export const PersistentPanelBody = (props) => {
    const newProps = {
        ...props,
    };

    newProps.id = newProps.id ?? newProps.title.toLowerCase().replace(/ /g, '');
    const initialOpen = newProps.initialOpen ?? true;
    delete newProps.initialOpen;

    const {
        panelBodyState,
    } = useSelect((select) => {
        const panelBodyState = select(editorStore).getPanelBodyState(newProps.id) ?? initialOpen;

        return {
            panelBodyState,
        };
    });

    const {
        setPanelBodyState,
    } = useDispatch(editorStore);

    const propOnToggle = newProps.onToggle;
    delete newProps.onToggle;

    const isOpened = newProps.opened ?? panelBodyState;
    delete newProps.opened;

    const togglePanelState = (isOpened) => {
        setPanelBodyState(newProps.id, isOpened);

        if (propOnToggle) {
            propOnToggle(isOpened);
        }

        return;
    };

    return (
        <PanelBody {...props} opened={isOpened} onToggle={togglePanelState}>
            {newProps.children}
        </PanelBody>
    );
}

export default PersistentPanelBody;
