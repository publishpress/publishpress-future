import {
    useState,
    useEffect,
    useRef,
    Fragment
} from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

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
    compactView = false
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

    return (
        <Fragment>
            { offset && (
                <div className={'publishpress-future-date-preview' + compactClass}>
                    <h4>{ label }</h4>
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
