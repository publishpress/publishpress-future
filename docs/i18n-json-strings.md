# JSON i18n Strings in PublishPress Plugins

This project uses a custom i18n bootstrap for JavaScript strings via `@publishpress/i18n`.

We do **not** rely only on WordPress native JSON loading at runtime for our React/admin UI.
Instead, we inject translation JSON data into the page and consume it through a shared wrapper.

## Why this exists

- Keep a single i18n API for PublishPress Free/Pro UIs.
- Allow merging string sources (`publishpressI18nConfig` and `publishpressI18nProConfig`).
- Keep compatibility with locales that use legacy and hashed JSON file names.

## JS usage rules

- Use `@publishpress/i18n` in plugin UI code:
  - `import { __, _x, _n, sprintf } from '@publishpress/i18n';`
- Keep text domain as `post-expirator`.
- Do not mix translation helpers in the same component unless needed.
- For project strings, prefer:
  - `__()` from `@publishpress/i18n` with explicit `post-expirator` domain
- Avoid direct `@wordpress/i18n` usage for plugin UI strings, because that bypasses our merged data layer.

Domain behavior:

- `__('Text', 'post-expirator')`: resolves from merged PublishPress Free/Pro locale data first, then falls back to WordPress for that domain.
- `__('Text')` (domain omitted, null, or empty): intentionally delegates to WordPress global translations (`wp.i18n.__`) and does **not** scan merged plugin domains.
- For plugin-owned strings, always pass `post-expirator` explicitly to avoid ambiguous global matches.
- When a payload uses WordPress JSON shape (`domain` + `locale_data.messages`), the wrapper mirrors `messages` into `locale_data[domain]` before merging so explicit domain lookups continue to work.

Example:

```jsx
import { __ } from '@publishpress/i18n';

const title = __('Export', 'post-expirator');
const wpCoreLabel = __('Settings');
```

## Webpack externals requirement

`@publishpress/i18n` is provided globally by the `publishpress-i18n` script.
Because of that, webpack must map this package to the global object instead of bundling it.

In `webpack.config.js`, keep this in `externals`:

```js
externals: {
  '@publishpress/i18n': 'publishpress.i18n'
}
```

Important notes:

- If this mapping is removed, built bundles may include unresolved imports or use the wrong i18n source.
- If the global name changes, update both the script bootstrap (`assets/jsx/i18n.jsx`) and webpack mapping.
- Ensure `publishpress-i18n` is enqueued before scripts that import `@publishpress/i18n`.

## PHP bootstrap requirements

The plugin must enqueue the shared i18n script and localize translation payload:

- `publishpress-i18n` script is enqueued by `Plugin::initializeI18nForScripts()`.
- Locale payload is exposed as `window.publishpressI18nConfig`.
- Free/Pro payloads are merged by `assets/jsx/i18n.jsx`.

Current loader behavior in `Plugin::getLocalizedTranslations()`:

- Always returns a stable `locale_data` structure.
- Supports both file patterns:
  - `post-expirator-<locale>.json`
  - `post-expirator-<locale>-<hash>.json`
- Safely ignores invalid/unreadable JSON files.
- Merges domains/keys from all matched files.

## Building language files

- After string changes, compile translations:
  - `composer translate:compile`
- If you changed JSON-specific strings/files, regenerate JSON translations:
  - `composer translate:json`
- If JSX changed, also rebuild JS:
  - `composer build:js`

## Important caveats

- If `@publishpress/i18n` is not available on a page, UI strings may fail or fallback unexpectedly.
- Locale issues can affect multiple tabs/screens at once because they share this bootstrap.
- Keep `post-expirator` as the domain across PHP and JS strings.

## Troubleshooting checklist

1. Confirm page enqueues `publishpress-i18n`.
2. Confirm `window.publishpressI18nConfig` exists in browser.
3. Confirm locale JSON files exist in `languages/` (legacy or hashed name).
4. Rebuild translations (`composer translate:compile`) and JSON files (`composer translate:json`).
5. Clear cache and reload admin page.
