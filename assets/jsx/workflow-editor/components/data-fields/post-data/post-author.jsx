import { __ } from "@publishpress/i18n";
import {
    useMemo,
    useEffect,
    useState
} from "@wordpress/element";
import {
    __experimentalVStack as VStack,
    SelectControl
} from "@wordpress/components";
import ToggleInlineSetting from "./toggle-inline-setting";
import apiFetch from "@wordpress/api-fetch";

const { apiUrl, nonce, workflowNonce } = window.futureWorkflowEditor;

let authorsPromise = null;
let cachedAuthors = null;

const getAuthors = () => {
    if (cachedAuthors) {
        return Promise.resolve(cachedAuthors);
    }

    if (!authorsPromise) {
        authorsPromise = apiFetch({
            path: `${apiUrl}/authors`,
            headers: {
                'X-WP-Nonce': nonce,
                'X-PP-Workflow-Nonce': workflowNonce,
            },
        }).then(response => {
            cachedAuthors = response;
            return cachedAuthors;
        });
    }
    return authorsPromise;
};

const getAuthorOptions = (authors) => {
    return authors.map(author => ({
        value: author.id,
        label: author.name,
    }));
};

export const PostAuthorControl = ({
    name,
    label,
    defaultValue,
    onChange,
    checkboxLabel
}) => {
    const [authors, setAuthors] = useState([]);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        setIsLoading(true);

        getAuthors()
            .then(setAuthors)
            .catch(error => {
                dispatch('core/notices').createErrorNotice(
                    __('Unable to load the list of authors. Please try again.', 'post-expirator')
                );
            })
            .finally(() => {
                setIsLoading(false);
            });
    }, []);

    defaultValue = {
        authors: [authors[0]?.id],
        update: false,
        ...defaultValue
    };

    const authorOptions = useMemo(() => getAuthorOptions(authors), [authors]);

    const valuePreview = useMemo(() => {
        if (!defaultValue.update || defaultValue.authors.length === 0) {
            return __('Do not update', 'post-expirator');
        }

        return defaultValue.authors.map(
            authorId => authors.find(a => parseInt(a.id) === parseInt(authorId))?.name
        ).join(', ');
    }, [defaultValue, authors]);

    return (
        <>
            <ToggleInlineSetting
                name={name}
                label={label}
                valuePreview={valuePreview}
                defaultValue={defaultValue}
                checkboxLabel={checkboxLabel}
                onChange={onChange}
                onUncheckUpdate={() => onChange(name, null)}
                isLoading={isLoading}
            >
                <VStack>
                    <SelectControl
                        label={__('Author', 'post-expirator')}
                        value={defaultValue.authors[0]}
                        options={authorOptions}
                        onChange={value => {
                            onChange(name, {
                                authors: [value],
                                update: true
                            });
                        }}
                    />
                </VStack>
            </ToggleInlineSetting>
        </>
    )
}

export default PostAuthorControl;
