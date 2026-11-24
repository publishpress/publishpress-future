# Future MCP request examples

Discover the connected site's actual IDs, actions, settings and choices before writing. Ask for missing targets, dates, timezone or ambiguous workflow scope. Use previews and fresh revisions where supported, then read back the saved state.

| Request | Expected workflow |
|---|---|
| “Change this post to Draft next Friday at 6pm.” | Resolve the post and exact date/timezone; discover permitted actions/statuses; read its hash; preview and schedule; verify the UTC instant and action. |
| “Remove these categories from these three posts next month.” | Verify post and term IDs plus the date; read each post's native choices and hash; validate the entire batch; save and verify each target. |
| “Turn off the Future Action on post 42.” | Read the actual action; preview `enabled=false` with its hash; cancel through the scheduler; verify disabled state. |
| “Make new pages automatically use this action after two weeks.” | Read Post Types defaults for `page`; clarify which native action; patch `autoEnable`, default action and native custom date offset; existing page schedules remain unchanged. |
| “Enable overdue action notification emails.” | Discover the Notifications settings group; distinguish `pastDueActionsNotification` from ordinary action emails; preview/save only the requested field. |
| “Clear the extra notification recipients.” | Read the appropriate Notifications list; send an empty JSON array for that field; preserve other notification settings. |
| “Show a short expiration message at the end of posts.” | Discover Display settings and current footer; clarify text/date format if missing; preview the native footer fields and verify saved output settings. |
| “Change the date picker to a 24-hour clock.” | Read Admin settings; use the discovered `timeFormatForDatePicker` choice `24h`; preserve the column style and labels. |
| “Create a workflow that changes posts to Draft after a delay.” | Discover native step definitions, input shapes and handles; clarify trigger/query/delay; create and inspect a disabled draft; enable only when requested and valid. |
| “Add an email step to this workflow.” | Read its graph and revision; discover the native email step and expression/recipient fields; preserve other nodes/connections; preview, save and read back. |
| “Copy this workflow but leave it disabled.” | Verify the source workflow/hash; duplicate into a new draft; return the new ID and disabled status. |
| “Run this selected workflow on this post now.” | Read the post's selection and eligible native choices; preview with its latest hash; explicitly dispatch once; inspect scheduled actions for later work. |
| “Click this Pro workflow button for this post.” | Discover eligible post-row actions; use exact workflow/step IDs and hash; obtain any required confirmation; dispatch once and inspect the native outcome. |
| “Use my custom expiry field for pages.” | Read Pro settings for `page`; discover the exact mapping key; patch that entry and mapping enablement; preserve other post types and mappings. |
| “Approve my workflow notification.” | List the authenticated recipient's notifications; read the selected message/hash and offered options; clarify ambiguous responses; preview and respond once. |
| “Retry the failed workflow.” | Inspect the failed action and related workflow; explain that blind failed-job replay is unavailable; identify and correct the cause, then use the appropriate native administration workflow. |
| “Import these workflows.” | Validate all native graph records with `dry_run`; create new disabled drafts; report new IDs without overwriting or activating sources. |
| “Export all configuration.” | Clarify intended scopes; enumerate authorized workflow IDs, groups and supported post types; issue bounded export requests; exclude credentials and execution contexts. |

If a write fails or the transport disconnects, read the affected state before retrying. A dispatch acknowledgement is not confirmation that all downstream effects completed. A Free site must report unavailable Pro operations rather than approximating them through arbitrary post metadata or options.
