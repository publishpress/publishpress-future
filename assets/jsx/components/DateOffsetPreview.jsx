import {
    useState,
    useEffect,
    useRef,
    Fragment
} from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { useSpeakOnChange } from './a11yUtils';
const { apiFetch } = wp;

require('./css/dateOffsetPreview.css');

export const DateOffsetPreview = ({
    offset,
    label,
    labelDatePreview,
    labelOffsetPreview,
    setValidationErrorCallback,
    setHasPendingValidationCallback,
    setHasValidDataCallback,
    compactView = false,
    headingLevel
}) => {
    const [offsetPreview, setOffsetPreview] = useState('');
    const [currentTime, setCurrentTime] = useState();

    const apiRequestControllerRef = useRef(new AbortController());

    const validateDateOffset = () => {
        if (offset) {
            const controller = apiRequestControllerRef.current;

            if (controller) {
                controller.abort();
            }

            apiRequestControllerRef.current = new AbortController();
            const { signal } = apiRequestControllerRef.current;

            setHasPendingValidationCallback(true);

            apiFetch({
                path: addQueryArgs(`publishpress-future/v1/settings/validate-expire-offset`),
                method: 'POST',
                data: {
                    offset
                },
                signal,
            }).then((result) => {
                setHasPendingValidationCallback(false);

                setHasValidDataCallback(result.isValid);
                setValidationErrorCallback(result.message);

                if (result.isValid) {
                    setOffsetPreview(result.calculatedTime);
                    setCurrentTime(result.currentTime);
                } else {
                    setOffsetPreview('');
                }
            }).catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }

                setHasPendingValidationCallback(false);
                setHasValidDataCallback(false);
                setValidationErrorCallback(error.message);
                setOffsetPreview('');
            });
        }
    }

    useEffect(() => {
        validateDateOffset();
    }, [offset]);

    const compactClass = compactView ? ' compact' : '';

    // The compact view keeps h4 because its CSS targets `.compact h4`.
    const level = headingLevel || (compactView ? 4 : 3);
    const HeadingTag = 'h' + Math.min(Math.max(parseInt(level, 10) || 3, 2), 6);
    // Keep the original h4 look when another level is used.
    const headingStyle = HeadingTag !== 'h4' ? { fontSize: '1em', margin: '1.33em 0' } : undefined;

    // Announce the computed date (politely, debounced) so screen reader users hear the result of the offset they typed.
    useSpeakOnChange(
        offset && offsetPreview ? `${labelOffsetPreview}: ${offsetPreview}` : '',
        'polite',
        1000
    );

    return (
        <Fragment>
            { offset && (
                <div className={'publishpress-future-date-preview' + compactClass}>
                    <HeadingTag style={headingStyle}>{ label }</HeadingTag>
                    <div className="publishpress-future-date-preview-body">
                        <div>
                            <span className="publishpress-future-date-preview-label">{ labelDatePreview }: </span>
                            <span className="publishpress-future-date-preview-value">{currentTime}</span>
                        </div>
                        <div>
                            <span className="publishpress-future-date-preview-label">{ labelOffsetPreview }: </span>
                            <span className="publishpress-future-date-preview-value">{offsetPreview}</span>
                        </div>
                    </div>
                </div>
            )}
        </Fragment>
    )
}

export default DateOffsetPreview;
