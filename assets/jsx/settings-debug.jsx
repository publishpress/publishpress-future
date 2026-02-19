import { useState, useEffect, useRef } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { createRoot } from 'react-dom/client';

const config = window.publishpressFutureSettingsDebug || {};
const text = config.text || {};

function DebugLogAutoRefresh() {
    const [autoRefresh, setAutoRefresh] = useState(false);
    const [refreshInterval, setRefreshInterval] = useState(10);
    const [isRefreshing, setIsRefreshing] = useState(false);
    const [error, setError] = useState('');
    const [lastRefreshedSeconds, setLastRefreshedSeconds] = useState(null);
    const intervalRef = useRef(null);
    const lastRefreshTimeRef = useRef(null);
    const tickRef = useRef(null);

    const fetchLogs = async () => {
        const logCount = document.getElementById('log-count')?.value || 500;
        const groupByChecked = document.querySelector('input[name="group_by_request"]:checked');
        const groupByRequest = groupByChecked ? groupByChecked.value : 1;
        const triggerOnly = document.querySelector('input[name="trigger_activated_only"]')?.checked ? 1 : 0;

        setIsRefreshing(true);
        setError('');
        try {
            const apiRoot = config.apiRoot || '';
            const data = await apiFetch({
                url: `${apiRoot}?log_count=${logCount}&group_by_request=${groupByRequest}&trigger_activated_only=${triggerOnly}`,
                headers: {
                    'X-WP-Nonce': config.nonce || '',
                },
            });
            const textarea = document.querySelector('.pp-debug-log textarea');
            if (textarea) {
                if (data.log_text) {
                    textarea.value = data.log_text;
                } else if (data.total_logs_unfiltered === 0) {
                    textarea.value = text.emptyLog || 'Debugging table is currently empty.';
                } else {
                    textarea.value = text.noResults || 'No results match the current filter.';
                }
                textarea.scrollTop = textarea.scrollHeight;
            }
            lastRefreshTimeRef.current = Date.now();
            setLastRefreshedSeconds(0);
        } catch (e) {
            setError(text.refreshError || 'Failed to refresh log data.');
        } finally {
            setIsRefreshing(false);
        }
    };

    useEffect(() => {
        if (autoRefresh) {
            fetchLogs();
            intervalRef.current = window.setInterval(fetchLogs, refreshInterval * 1000);
            tickRef.current = window.setInterval(() => {
                if (lastRefreshTimeRef.current !== null) {
                    setLastRefreshedSeconds(Math.floor((Date.now() - lastRefreshTimeRef.current) / 1000));
                }
            }, 1000);
        } else {
            window.clearInterval(intervalRef.current);
            window.clearInterval(tickRef.current);
            setLastRefreshedSeconds(null);
        }
        return () => {
            window.clearInterval(intervalRef.current);
            window.clearInterval(tickRef.current);
        };
    }, [autoRefresh, refreshInterval]);

    return (
        <div className="pp-debug-log-option pp-debug-log-autorefresh">
            <label className="pp-checkbox-label">
                <input
                    type="checkbox"
                    checked={autoRefresh}
                    onChange={(e) => setAutoRefresh(e.target.checked)}
                />
                {' '}{text.autoRefresh || 'Auto-refresh'}
            </label>
            {autoRefresh && (
                <>
                    <label htmlFor="pp-autorefresh-interval" style={{ marginLeft: '10px' }}>
                        {text.refreshInterval || 'Refresh interval:'}
                    </label>
                    <select
                        id="pp-autorefresh-interval"
                        value={refreshInterval}
                        onChange={(e) => setRefreshInterval(Number(e.target.value))}
                        style={{ marginLeft: '5px' }}
                    >
                        <option value={5}>5 {text.seconds || 'seconds'}</option>
                        <option value={10}>10 {text.seconds || 'seconds'}</option>
                        <option value={30}>30 {text.seconds || 'seconds'}</option>
                        <option value={60}>60 {text.seconds || 'seconds'}</option>
                    </select>
                    {isRefreshing && (
                        <span style={{ marginLeft: '10px', fontStyle: 'italic' }}>
                            {text.refreshing || 'Refreshing...'}
                        </span>
                    )}
                    {!isRefreshing && lastRefreshedSeconds !== null && (
                        <span style={{ marginLeft: '10px', color: '#666' }}>
                            {text.lastRefreshed || 'Last refreshed:'} {lastRefreshedSeconds} {text.secondsAgo || 'seconds ago'}
                        </span>
                    )}
                    {error && (
                        <span style={{ marginLeft: '10px', color: 'red' }}>{error}</span>
                    )}
                </>
            )}
        </div>
    );
}

const container = document.getElementById('pp-debug-log-autorefresh');
if (container) {
    const root = createRoot(container);
    root.render(<DebugLogAutoRefresh />);
}
