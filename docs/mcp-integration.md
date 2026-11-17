# PublishPress Future MCP integration

PublishPress Future exposes actions through the WordPress Abilities API. PublishPress Hub can make selected actions available to a compatible AI service through the separately installed WordPress MCP Adapter.

## Connection and access

Use WordPress 6.9 or later and MCP Adapter 0.7.1 or later for Hub connections. Enable AI connections in Hub, choose allowed actions and save before connecting the AI app. Writes also require Hub's write switch and individual action selection. Future continues to work on its supported WordPress versions when the Abilities API is unavailable.

Every action checks the connected WordPress account's capabilities. Hub selection does not grant permissions. An Administrator role cannot override a denied capability or an application-password restriction. Future's actions are private to explicitly configured MCP servers; the Adapter's default public discovery cannot expose or execute them.

Action names begin with `publishpress-future/`. Start with `list-features` to distinguish post actions, workflows, settings and Pro features. Read the relevant discovery action for current values, exact field types, choices and revision hashes. Post IDs and taxonomy term IDs must be verified rather than guessed.

## Future Actions for posts

`get-post-action` reads the same simple Future Action used in the classic editor, block editor, Quick Edit and Bulk Edit. `get-post-action-choices` supplies native action/status choices, supported defaults and permitted taxonomy choices. Term results are paginated with `page` and `per_page`.

`update-post-action` schedules, changes or cancels an action. Supply the latest `expected_hash` from the read result. Only supplied fields change. `enabled=false` cancels the action through the native scheduler. Dates must be ISO 8601 timestamps with a timezone offset, for example `2027-01-15T18:00:00+01:00`, and must be in the future. Read results use UTC timestamps and include the site timezone. The native scheduler cannot represent both occurrences of an ambiguous daylight-saving time; an unrepresentable instant is rejected before saving.

Native permissions for status changes, publishing, deleting, sticky posts and assigning terms apply in addition to `publishpress_future_expire_post` and permission to edit each target post. Pro custom statuses must be enabled for the post type. A workflow action needs an active, readable workflow with the Future Actions trigger.

Use `dry_run=true` to validate without scheduling. `bulk-update-post-actions` validates every explicit target and revision before writing. A failed save attempts to restore schedules already changed; an incomplete recovery is reported and requires reading all affected posts before retrying.

## Action Workflows

`get-workflow-step-types` returns the native trigger, action and advanced step definitions, connection handles, field defaults, validation metadata and input schemas. It also returns current editor choices for post types, statuses, taxonomies, roles and recurring schedules. Pro step availability reflects the active edition.

Use `list-workflows` and `get-workflow` to inspect existing workflows. `save-workflow` creates a disabled draft by default or patches a known workflow using its current `expected_hash`. `update-workflow-step` changes a verified node while preserving the rest of the graph. `duplicate-workflow` creates a disabled copy. `delete-workflow` moves a workflow to Trash; permanent deletion is not supported.

Graphs use the native `nodes`, `edges` and optional `viewport` data. Use discovered step names, unique IDs/slugs, actual handles and each field's `inputSchema`. A status field, an expression field and a post-input field use different native shapes; their labels do not make them interchangeable. Graphs are bounded to 200 nodes and 500 connections. Unavailable steps, unknown settings and invalid connections are rejected. Custom action hooks must be allowed by the site's native hook allowlist.

Reading/editing workflows requires the native workflow-editing capability and relevant object permission. Creating also checks the workflow post type's creation capability. Publishing, editing an enabled workflow, disabling and deleting require the corresponding native publish/unpublish and object capabilities. Enabling checks required fields and connections. Debug settings are available through the workflow's typed `debug` fields; experimental and Pro steps retain their native configuration gates.

## Workflows on individual posts

`get-post-workflows` returns selected manual workflows and the native eligible choices for one editable post. `update-post-workflows` replaces that selection using its current hash. Removing a selection cancels its pending steps as in the editor; cancellation cannot be rolled back. Adding a selection does not start it.

`run-post-workflow` explicitly dispatches one selected, enabled, eligible workflow through the native trigger. Eligibility includes the workflow's native post query and permission to read it. The response acknowledges dispatch; downstream steps can run asynchronously, wait for an event or require interaction. It does not certify that every step completed. Use scheduled-action reads to inspect progress. Preview validates without dispatching.

With Pro, `get-post-row-actions` discovers eligible workflow buttons for one post. `run-post-row-action` uses the verified workflow and step IDs and latest hash. A button requiring confirmation also requires `confirmed=true` for execution. Native post-query, edition and object checks apply.

## Settings

`get-plugin-settings` discovers one group: `general`, `notifications`, `display`, `admin` or `advanced`. `update-plugin-settings` accepts a typed patch for that group with its latest hash. Use JSON booleans for checkboxes, integers for numeric fields and arrays for role/email lists, including empty arrays where clearing is supported. Strings such as `"false"` are rejected. Omitted and unrelated values remain intact. General role choices control Future scheduling access and retain Administrator scheduling access; they do not assign user roles.

`list-post-types`, `get-post-type-settings` and `update-post-type-settings` cover native Post Types defaults, notification addresses, taxonomies, automatic enabling and date offsets. These settings configure subsequent scheduling; they do not reschedule existing posts. Settings actions require `manage_options`; taxonomy assignment and configuration locks are checked where applicable.

Pro adds `get-pro-settings` and `update-pro-settings`. These cover the global scheduling base date and one supported post type's custom statuses, metadata mappings, hidden fields and metabox visibility. Nested patches preserve omitted mapping entries and other post types. Discover exact native metadata keys before editing. Licensing credentials are excluded. Pro requires a matching Free package containing these shared actions.

## Scheduled actions, notifications and diagnostics

`list-scheduled-actions` and `get-scheduled-action` expose permitted Future-owned post actions and workflow steps. They exclude foreign scheduler groups, maintenance hooks and raw execution context. `cancel-scheduled-action` and `run-scheduled-action` operate on verified pending actions using the latest hash and native services. Running checks related object and action-specific permissions again. Running is irreversible and is not an idempotent retry mechanism; failed jobs are not cloned or blindly replayed.

Pro notification reads return only messages owned by the authenticated recipient. `respond-to-notification` requires the current hash and an offered response on a pending, unread notification. It does not let an administrator impersonate another recipient. Responses can resume workflows and cannot be rolled back.

`get-debug-status` returns logging state and bounded counts. The Advanced settings group controls the native debug switch, subject to site locks. Full log bodies and execution contexts remain in the native Debug screen.

## Configuration transfer

`export-configuration` exports explicitly selected workflow IDs, settings groups and post types using Future's backup envelope. Requests are bounded; omitted scopes are not exported. Workflow object permissions still apply.

`import-workflows` validates all supplied workflow records before creating disabled drafts with new IDs. It never overwrites or enables an existing workflow. Use its preview before creating drafts. Settings are applied through their individual typed update actions with fresh hashes; arbitrary option import is not exposed.

## Limits and recovery

MCP actions do not manage licenses, update plugins, execute arbitrary PHP/SQL, mutate arbitrary options, expose account-switching sessions or control third-party form/product configuration. Native integrations can still trigger configured workflows through their own events.

After updating Free or Pro, refresh the AI app's tool discovery if it uses an older schema. An expired transport session must be reinitialized by the client; a tool on that session cannot reconnect the app. Reuse valid credentials. Read affected data before retrying an uncertain write, particularly workflow execution, notification responses and cancellation.

See [feature coverage](mcp-feature-coverage.md) and [request examples](mcp-prompt-examples.md).
