import { useState } from "@wordpress/element";

const {
    settings,
    text
} = publishpressFutureSettingsAdvanced;

export const ScheduledStepsCleanupSettings = () => {
    const [cleanupStatus, setCleanupStatus] = useState(settings.scheduledStepsCleanupStatus);
    const [cleanupRetention, setCleanupRetention] = useState(settings.scheduledStepsCleanupRetention);

    return (
        <>
            <th scope="row">{text.scheduledStepsCleanup}</th>
            <td>
                <fieldset className="pp-a11y-fieldset" style={{ border: 0, margin: 0, padding: 0, minWidth: 0 }}>
                <legend className="screen-reader-text">{text.scheduledStepsCleanup}</legend>
                <div className="pp-settings-field-row">
                    <input type="radio"
                        checked={cleanupStatus}
                        name="future-step-schedule-cleanup"
                        id="future-step-schedule-cleanup-enabled"
                        onChange={() => setCleanupStatus(true)}
                        value="1"
                        />
                    <label htmlFor="future-step-schedule-cleanup-enabled">{text.scheduledStepsCleanupEnable}</label>
                    <p className="description offset">{text.scheduledStepsCleanupEnableDesc}</p>
                    {cleanupStatus && (
                        <>
                            <div className="pp-settings-field-row" style={{ marginLeft: '24px', marginTop: '12px', marginBottom: '12px' }}>
                                <label htmlFor="future-step-schedule-cleanup-retention"
                                    style={{ marginRight: '4px' }}
                                >
                                    {text.scheduledStepsCleanupRetention}</label>
                                <input type="number"
                                    id="future-step-schedule-cleanup-retention"
                                    value={cleanupRetention}
                                    placeholder="30"
                                    name="future-step-schedule-cleanup-retention"
                                    style={{ width: '60px' }}
                                    onChange={(e) => setCleanupRetention(e.target.value)}
                                />
                                <span style={{ marginLeft: '4px' }}>{text.days}</span>
                                <p className="description">
                                    {text.scheduledStepsCleanupRetentionDesc}
                                </p>
                            </div>
                        </>
                    )}
                </div>
                <div className="pp-settings-field-row">
                    <input type="radio"
                        checked={!cleanupStatus}
                        name="future-step-schedule-cleanup"
                        id="future-step-schedule-cleanup-disabled"
                        onChange={() => setCleanupStatus(false)}
                        value="0"
                        />
                    <label htmlFor="future-step-schedule-cleanup-disabled">{text.scheduledStepsCleanupDisable}</label>
                    <p className="description offset">{text.scheduledStepsCleanupDisableDesc}</p>
                </div>
                </fieldset>
            </td>
        </>
    )
}
