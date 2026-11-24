# Adapt a plugin to translation delivery

For this change, language files stay in the built plugin package. `publishpress/bundled-translations` 1.0.6 loads those files ahead of a WordPress.org language pack. PublishPress Hub downloads later translation updates. The plugin does not call Weblate, and it does not call the package server.

The Hub notice stays off until the plugin opts in for its own text domain. Future 4.11.2 showed that notice on every admin screen. Do not repeat that.

A later change removes the language files from the built package only. The files stay in the git repository. Build them with the same translation scripts, and commit the same files. Do not delete the `languages` directory from the repo in this change or in that later change.

On release, `language-pack.yml` zips the committed `.po`, `.mo`, and `.json` files into `{text-domain}-locales.zip` and attaches that zip to the GitHub release. The package server downloads that zip and stores one zip per text domain and locale. The workflow does not compile the files, and the plugin does not send them.

## 1. Require library 1.0.6

In `lib/composer.json`, require `publishpress/bundled-translations` at `^1.0.6`.

`^1.0` still allows 1.0.5. Version 1.0.5 cannot limit the notice to a screen list.

If `lib/composer.json` has no `publishpress/bundled-translations` requirement, add `^1.0.6`. If the requirement is `^1.0`, `^1.0.0`, or `^1.0.3`, raise it to `^1.0.6`. Checked copies of Blocks, Cart, Future, Planner, Revisions, Shortlinks, Statuses, Tag Groups, and Taxonomies, Free and Pro, still use one of those lower constraints.

Update the library from the `lib` directory and commit the lockfile and `lib/vendor`.

```bash
composer update publishpress/bundled-translations
```

## 2. Load the library

Load this file before `plugins_loaded`:

`lib/vendor/publishpress/bundled-translations/core/include.php`

On `plugins_loaded`, create one `PublishPress\BundledTranslations\BundledTranslations` instance and call `init()`.

Pass these three arguments:

1. The text domain from the plugin header of that edition. Free and Pro can use different domains.
2. The absolute path of the bundled `languages` directory.
3. The absolute path of the main plugin file.

## 3. Opt in to the Hub notice

Add both filters in the main plugin file. For every other text domain, return the incoming value. Do not replace the screen list. Append this plugin's screen ids.

```php
add_filter('publishpress_bundled_translations_hub_notice_enabled', function ($enabled, $domain) {
    if ($domain === 'plugin-text-domain') {
        return true;
    }

    return $enabled;
}, 10, 2);

add_filter('publishpress_bundled_translations_hub_notice_screens', function ($screens, $domain) {
    if ($domain !== 'plugin-text-domain') {
        return $screens;
    }

    if (! is_array($screens)) {
        $screens = [];
    }

    $screens[] = 'toplevel_page_plugin-slug';
    $screens[] = 'plugin-slug_page_plugin-settings';

    return $screens;
}, 10, 2);
```

## 4. Collect the screen ids

Open each admin page that belongs to the plugin. Read `get_current_screen()->id`.

WordPress builds that id in `get_plugin_page_hookname()`:

1. A top-level menu uses `toplevel_page_{menu_slug}`.
2. A submenu uses `{menu_hook}_page_{menu_slug}`. The menu hook is `sanitize_title()` of the top-level menu title. A menu titled Future uses the hook `future`, so the settings screen is `future_page_publishpress-future-settings`.
3. `add_submenu_page()` with an empty parent uses `admin_page_{menu_slug}`.
4. A post type list uses `edit-{post_type}`.

List only this plugin's pages. Include each id the plugin already checks in its own enqueue code when two ids exist for one page.

Future uses these ids:

- `toplevel_page_publishpress-future`
- `future_page_publishpress-future-settings`
- `edit-ppfuture_workflow`
- `admin_page_future_workflow_editor`
- `future_page_publishpress-future-scheduled-actions`
- `admin_page_publishpress-future-scheduled-actions`

## 5. Check the notice

The notice is printed once per request. It is shown only when all of these are true:

1. The enabled filter returns true for this text domain.
2. The current screen id is in the screen list.
3. The site language, `get_locale()`, or the current user locale is a non-English locale with a bundled file. The file name is `{text-domain}-{locale}.mo`, `.po`, `.json`, or `.l10n.php`.
4. PublishPress Hub is not active. The library treats Hub as active when `PUBLISHPRESS_HUB_VERSION` is defined.
5. The user has the `install_plugins` capability.
6. The user has not dismissed the notice on this site.

Dismiss stores the user option `pp_bundled_translations_hub_notice_dismissed`. The link target is `https://publishpress.com/hub`.

## Do not

- Do not enable the notice for another plugin's text domain.
- Do not return a new screen list that drops ids another callback added.
- Do not download a language pack from the plugin.
- Do not write translation files into `wp-content/languages/plugins/`.
- Do not remove the `languages` directory from the repository. A later change omits those files from the built package. It does not change the script that builds them, and it does not stop the commit of those files.
