import { createRoot } from '@wordpress/element';
import { ScheduledStepsCleanupSettings } from './workflow-editor-settings/scheduled-steps-cleanup';

const {
    settingsTab
} = publishpressFutureSettingsAdvanced;

if ('advanced' === settingsTab) {
    const scheduledStepsCleanupContainer = document.getElementById('scheduled-steps-cleanup-settings');

    if (scheduledStepsCleanupContainer) {
        const root = createRoot(scheduledStepsCleanupContainer);
        root.render(<ScheduledStepsCleanupSettings />);
    }
}
