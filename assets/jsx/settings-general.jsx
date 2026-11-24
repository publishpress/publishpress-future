/*
 * Copyright (c) 2025, Ramble Ventures
 */

import { DateOffsetPreview } from "./components";
import { useControlA11yAttributes } from "./components/a11yUtils";

import {
    StrictMode,
    useState,
    useEffect,
    createRoot
} from "@wordpress/element";

const { text } = window.publishpressFutureSettingsGeneralConfig;

const SettingsFormPanel = (props) => {
    const [isValidForm, setIsValidForm] = useState(true);
    const [validationError, setValidationError] = useState('');
    const [hasPendingValidation, setHasPendingValidation] = useState(false);
    const [offset, setOffset] = useState('');

    const onHasValidationError = (errorMessage) => {
        if (errorMessage) {
            setIsValidForm(false);
            setValidationError(errorMessage);
        } else {
            setIsValidForm(true);
            setValidationError('');
        }
    }

    const onValidationStarted = (inProgress) => {
        setHasPendingValidation(inProgress);
    }

    const onValidationFinished = (isValid) => {
        setHasPendingValidation(false);
        setIsValidForm(isValid);
    }

    const hasError = ! isValidForm && !! validationError;
    const errorId = 'expired-custom-expiration-date-error';

    // Link the error to the offset field (rendered by PHP) and flag it as invalid.
    useControlA11yAttributes('expired-custom-expiration-date', hasError ? errorId : '', hasError);

    useEffect(() => {
        jQuery('#expired-custom-expiration-date').on('keyup', function () {
            setOffset(jQuery(this).val());
        });

        setOffset(jQuery('#expired-custom-expiration-date').val());
    }, []);

    return (
        <StrictMode>
            <DateOffsetPreview
                offset={offset}
                label={text.datePreview}
                labelDatePreview={text.datePreviewCurrent}
                labelOffsetPreview={text.datePreviewComputed}
                setValidationErrorCallback={onHasValidationError}
                setHasPendingValidationCallback={onValidationStarted}
                setHasValidDataCallback={onValidationFinished}
                headingLevel={4}
                />

            {hasError && (
                <div className="publishpress-future-notice publishpress-future-notice-error" id={errorId} role="alert">
                    <p>{text.error}: {validationError}</p>
                </div>
            )}
        </StrictMode>
    )
};

const container = document.getElementById("expiration-date-preview");

if (container) {
    const component = (<SettingsFormPanel />);

    createRoot(container).render(component);
}
