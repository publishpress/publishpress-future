/**
 * Copy text using the Clipboard API.
 *
 * @param {string} text
 * @return {Promise<void>}
 */
async function copyTextToClipboardWithApi(text) {
    await navigator.clipboard.writeText(text);
}

/**
 * Copy text using the legacy execCommand approach for older browsers.
 *
 * @param {string} text
 * @return {void}
 */
function copyTextToClipboardLegacy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'absolute';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();

    const copied = document.execCommand('copy');
    document.body.removeChild(textarea);

    if (!copied) {
        throw new Error('Copy command failed');
    }
}

/**
 * Copy text to the clipboard with a fallback for older browsers.
 *
 * @param {string} text
 * @return {Promise<void>}
 */
export async function copyTextToClipboard(text) {
    if (navigator.clipboard?.writeText) {
        await copyTextToClipboardWithApi(text);
        return;
    }

    copyTextToClipboardLegacy(text);
}
