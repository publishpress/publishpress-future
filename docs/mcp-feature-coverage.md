# Future MCP feature coverage

All actions require native execution permissions and the site's exposure choices. Pro rows require the actual active Pro edition and the shared Free integration.

| Native feature | Discovery and reads | Supported writes | Permissions and limits |
|---|---|---|---|
| Classic editor, Gutenberg, Quick Edit and Bulk Edit Future Actions | `get-post-action`, `get-post-action-choices` | Schedule, patch, cancel; prevalidated explicit post batches | Future scheduling capability, each post's edit permission, native action/status/delete/sticky/taxonomy checks; enabled post type |
| Taxonomy actions and term choices | Native action/status/taxonomy choices; paginated terms | Set/add/remove existing terms through the scheduled native action | Matching taxonomy and assign-terms capability; no term creation |
| Pro custom statuses and workflow Future Actions | Native permitted statuses and readable workflows | Schedule enabled custom statuses or an active workflow with Future Actions trigger | Active edition, per-type configuration and native action permissions |
| Workflow list and editor | List/read workflows; native triggers, actions, advanced steps, field schemas, choices and handles | Create draft, patch graph/node/debug settings, duplicate disabled, publish/disable, Trash | Workflow edit/object/create/publish/unpublish/delete checks; native validation and edition gates; no permanent deletion |
| Pro workflow steps and integrations | Native available Pro definitions and field schemas | Configure native Pro steps within validated graphs | Active Pro; native event/query/configuration gates; no third-party account or product/form administration |
| Post workflow metabox | Saved selection and native eligible manual-trigger choices | Replace selection; explicitly dispatch a selected workflow | Scheduling/edit-post/read-workflow checks; native post query; deselection can cancel pending steps |
| Pro post-row workflow buttons | Eligible native button, step ID, query and confirmation settings | Explicitly dispatch a verified confirmed button | Same native target and trigger checks; preview never dispatches |
| General settings | Native offsets, calendar preference and allowed scheduling roles | Typed patch and preview | `manage_options`; role choices grant only the native Future scheduling capability; Administrator remains allowed |
| Notification settings | Native email toggles and configured address lists, including overdue alerts | Typed patch/clear lists and preview | `manage_options`; valid addresses; no message sent by changing settings |
| Display settings | Date/time formats, footer contents/style and shortcode container settings | Native sanitized patch and preview | `manage_options`; safe native wrappers, HTML and CSS |
| Admin settings | Metabox labels, column style and date-picker time format | Native typed patch and preview | `manage_options`; exact native choices |
| Advanced and Debug | Compression, cleanup/retention, experimental state, preserve-data and debug state/counts | Typed settings patch and preview | `manage_options`; site code locks; no raw log/context export or database maintenance |
| Post Types settings | Supported types, enabled state, native defaults and choices | Patch default action/status, automatic enable, metabox, taxonomy/terms, notifications and offset | `manage_options`, supported type, valid native choices and taxonomy permissions; existing posts unchanged |
| Pro settings | Base date, enabled custom statuses, metadata mapping, hidden fields and metabox state | Typed nested patch and preview | `manage_options`, active Pro; preserves other post types; no license credentials |
| Scheduled Actions | Future-owned pending/failed/completed/canceled/in-progress action summaries | Cancel or run a verified pending action now | `manage_options` plus related object and run-time native action checks; foreign/maintenance jobs excluded; no automatic failed-job replay |
| Pro notifications | Current recipient's bounded list and individual offered responses | Respond to a pending unread notification; preview | Authenticated recipient ownership on reads and writes; latest hash; no impersonation |
| Backup and export | Selected non-secret settings, post-type defaults and authorized workflows | Validate/import workflows into new disabled drafts; settings through typed per-group actions | `manage_options`, workflow permissions, bounded requests; no arbitrary options or source-ID overwrite |
| Licensing, plugin installation/update and maintenance | Not exposed | Not exposed | Use native administration and host tooling |

Workflow effects follow the native engine. Dispatch can schedule later work or wait for an event/response. Each action returns its immediate outcome, not a claim that asynchronous workflow steps have all completed.
