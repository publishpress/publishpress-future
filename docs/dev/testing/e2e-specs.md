# Playwright e2e case catalog

Case files under `docs/testing/cases/` are the source of truth for the browser suite. This pass is specs only. The next pass adds Playwright and implements one spec file per case.

`automation.implementations` stays empty until that spec file exists.

## Next pass layout

Modeled on PublishPress Cart:

- Playwright tests in `tests/playwright/<suite>/`, one `*.spec.ts` per case.
- `playwright.config.ts` at the repo root, aimed at the test site URL from `.env`.
- Composer scripts call `npm run test:e2e`. Codeception stays on unit and integration.
- Move `tests/Unit` and `tests/Integration` to `tests/codeception/` and point `codeception.yml` there.
- Leave `tests/Acceptance` and `tests/EndToEnd` in place until the Playwright cases cover them.

Do not copy Cart's Scribe catalog tooling in that pass unless we decide to.

## Suites

| Suite | Prefix | Cases | Later test path |
| --- | --- | ---: | --- |
| admin | AD | 20 | `tests/playwright/admin/` |
| editor | ED | 13 | `tests/playwright/editor/` |
| actions | AC | 14 | `tests/playwright/actions/` |
| public | PB | 3 | `tests/playwright/public/` |
| workflows | WF | 2 | `tests/playwright/workflows/` |
| workflow-editor | WE | 19 | `tests/playwright/workflow-editor/` |

71 cases. Edition is free. Pro step coverage is the lock shown in WE-016, not a Pro step matrix.

## Not in this catalog

- Instance protection (`tests/Acceptance/features/instance-protection.feature`). It needs a second plugin copy on disk and stays on Codeception.
- Metadata scheduling, imports, and the debug log viewer beyond the Diagnostics screen in AD-019.

## Admin (`AD`)

Future Actions, Settings, and Scheduled Actions.

- **AD-001** Future admin menu visibility and access
- **AD-002** Future menu opens Post Types tab
- **AD-003** Activate post type for Future actions
- **AD-004** Default Action for post persists after reload
- **AD-005** Taxonomy for post default action persists
- **AD-006** Automatically create actions for post
- **AD-007** Default Date/Time Offset persists
- **AD-008** Calendar Visibility default persists
- **AD-009** Role permissions for Future actions
- **AD-010** Enable Email Notification persists
- **AD-011** Email notification recipients persist
- **AD-012** Past-due actions notification persists
- **AD-013** Default date and time formats persist
- **AD-014** Post footer display settings persist
- **AD-015** Shortcode wrapper settings persist
- **AD-016** Metabox labels persist on Admin tab
- **AD-017** List column style and time format persist
- **AD-018** Preserve data on deactivation setting persists
- **AD-019** Diagnostics checks show Passed
- **AD-020** Scheduled Actions screen access

## Editor (`ED`)

Block editor, Classic Editor, Quick Edit, Bulk Edit, and the posts list.

- **ED-001** Panel visible when post type active
- **ED-002** Panel hidden when post type inactive
- **ED-003** Manual enable persists with Stick and date
- **ED-004** Auto-create default checked; uncheck persists
- **ED-005** Stick action and datetime persist in block editor
- **ED-006** Stick and datetime persist in Classic Editor
- **ED-007** Classic metabox hidden when post type inactive
- **ED-008** Quick Edit sets action visible on full edit
- **ED-009** Bulk add or update action on two posts
- **ED-010** Future Action column shows Stick date
- **ED-011** Custom metabox title and checkbox labels
- **ED-012** Corrupted meta loads with checkbox unchecked
- **ED-013** Corrupted meta in Classic Editor unchecked

## Actions (`AC`)

Set the action in the block editor, run the Action Scheduler queue from the harness, assert the post in wp-admin.

- **AC-001** Change status to Draft
- **AC-002** Change status to Private
- **AC-003** Change status to Trash
- **AC-004** Delete
- **AC-005** Stick
- **AC-006** Unstick
- **AC-007** Replace all categories
- **AC-008** Add extra categories
- **AC-009** Remove selected categories
- **AC-010** Remove all categories
- **AC-011** Future-dated action not run early
- **AC-012** Email notification when enabled
- **AC-013** No email when notifications disabled
- **AC-014** Corrupted future-action meta recovery

## Public (`PB`)

Footer placeholder is `ACTIONFULL`. Shortcode tag is `futureaction`.

- **PB-001** Post footer shows formatted action date and time
- **PB-002** Post footer hidden when display footer is disabled
- **PB-003** futureaction shortcode shows action date on the front end

## Workflows (`WF`)

- **WF-001** Action Workflows admin access and editor entry
- **WF-002** Workflow moves post to Draft immediately or after scheduled delay

## Workflow editor (`WE`)

Canvas at `admin.php?page=future_workflow_editor`. An empty required node field shows "The field %s is required." and does not disable Publish.

- **WE-001** New workflow shows trigger placeholder and welcome guide
- **WE-002** Welcome guide dismiss persists and can be reopened
- **WE-003** Save draft with a title lists workflow as Draft
- **WE-004** Publish a draft workflow changes button to Update
- **WE-005** Update a published workflow title
- **WE-006** Move workflow to trash from the sidebar
- **WE-007** Reload keeps title, nodes, connection and Draft setting
- **WE-008** Inserter opens with Triggers and Actions tabs and closes
- **WE-009** Inserter search finds free trigger and action
- **WE-010** Choosing a trigger replaces the placeholder
- **WE-011** Add an action from the trigger output
- **WE-012** Inspector sets status to Draft without saving
- **WE-013** Empty required node field shows validation message
- **WE-014** Delete the action node removes node and edge
- **WE-015** Auto Layout keeps both nodes and the connection
- **WE-016** Pro step is shown with badge and is not placed
- **WE-017** Fullscreen mode hides and restores the WP admin menu
- **WE-018** Mini map and controls panel toggle from Options
- **WE-019** Advanced Settings and Developer mode reveal extra panels
