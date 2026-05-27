import { TreeSelect } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const settings = window.futureWorkflowEditor;

export default function Recurrence({ label, disabled }) {
    const cronScheduleOptions = settings.cronSchedules.map((schedule) => {
        return {
            name: schedule.label,
            id: `cron_${schedule.value}`,
        };
    });

    const recurrenceOptions = [
        { name: __("Non-repeating", "post-expirator"), id: "single" },
        { name: __("Custom interval in seconds", "post-expirator"), id: "custom" },
        ...cronScheduleOptions
    ];

    return (
        <TreeSelect
            label={label}
            tree={recurrenceOptions}
            disabled={disabled}
        />
    );
}
