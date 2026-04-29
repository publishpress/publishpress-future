The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

[4.10.2] - 29 April, 2026

- Added: Added new language files for locales: ar, cs_CZ, da_DK, el, en_US, fa_IR, he_IL, or_RO, pl_PL, sv_SE, th, tr_TR, and vi.
- Fixed: WordPress.org translations now correctly take priority over custom or AI-generated strings, ensuring consistent language display while still merging all available sources.
- Fixed: Enhanced translations for JavaScript interfaces, particularly in the settings and workflow editor.
- Changed: Updated existing translations.

[4.10.1] - 20 April, 2026

- Fixed: Update language strings.
- Security: Stored XSS via `[futureaction]` shortcode (CVE-2026-5247, CVSS 5.5 Medium). Insufficient input sanitization allowed authenticated attackers (administrator-level or lower-privileged users when the shortcode is available to them) to inject arbitrary web scripts into pages. Thanks to zaim for the responsible disclosure.

[4.10.0] - 25 March, 2026

- Added: Add new trigger: Post is created - PRO, (Issue #1146).
- Added: Debug log view shows total logs displayed, session count, and log size (filter-aware; shows both filtered and total when filter applied).
- Added: Debug log timestamps now include milliseconds.
- Added: Debug log filter to show only requests where a workflow trigger was activated.
- Added: Debug log request_id column for request correlation, with migration for existing sites.
- Added: Debug log display option to toggle between time sequence and grouped by request views.
- Added: Debug log autorefresh option.
- Added: Workflow engine logs "Engine finished processing" on shutdown when debug is enabled.
- Added: Added cache for workflow execution context variables to increase performance when resolving variables repeated times, disabled by default (Issue #1581).
- Added: Added new language files for German, Finnish, Filipino, Indonesian, Japanese, Korean, Russian, and Yoruba translations.
- Added: Added new constant `PUBLISHPRESS_FUTURE_VARIABLES_CACHE` as a flag to enable a experimental cache for context variables in the workflow execution (Issue #1581).
- Changed: Debug log now defaults to grouped by request display.
- Changed: Move the scheduled actions "Run" button to it's own column, (Issue #1496).
- Changed: Workflow debug log messages rewritten for clarity and consistency (format, parentheses for details, [Workflow] prefix).
- Changed: Replace UUID generation with a more secure method in the workflow's execution ID, also incresing performance on huge sites (Issue #1579).
- Fixed: Improve performance on large multisites by memoizing table existence checks, using `information_schema` instead of `SHOW TABLES LIKE`, and creating the debug log table only on first use (Issue #1597).
- Fixed: Debug log enable/disable in Diagnostics and Tools tab now reflects the correct state immediately without needing a second page refresh.
- Fixed: Improved reliability of the "Post is Published" and "Post is Updated" triggers by fixing how post metadata is handled on block editor and when ACF is enabled (Issue #1312).
- Fixed: Fixed post saved related events not triggering when revisions is saved (Issue #1582).
- Fixed: Update .pot file.
- Deprecated: StepProcessorInterface::prepareLogMessage() in favor of direct logger sprintf methods.

[4.9.4] - 18 December, 2025

- Added: Enhance workflow management with custom capabilities (Issue #1540).
- Added: Introduced new workflow capabilities: edit_publishpress_workflows, publish_publishpress_workflows, unpublish_publishpress_workflows
- Added: Update REST API to use workflow-specific capabilities instead of generic edit_posts
- Added: Add X-PP-Workflow-Nonce verification to all workflow endpoints for enhanced security
- Fixed: Published pages via Future Actions show incorrect View links (Issue #1539).
- Fixed: Unable to edit imported workflow (Issue #1544).
- Fixed: "Query posts" step doesn't work when post type is not specified for custom posts, (Issue #1546)
- Fixed: Update ES-FR-IT translations (Issue #1537).

[4.9.3] - 11 December, 2025

- Added: Added new conditional operators for workflow steps: "Is empty" and "Is not empty", allowing you to check if a value or field is empty or not within conditional logic (Issue #1518).
- Fixed: Warning: Undefined array key "id" on an imported workflow, (Issue #1524)
- Fixed: Unable to re-schedule "On Schedule" trigger if it was already executed, (Issue #1527)
- Fixed: "Update post details" step not working with "Query posts" step, (Issue #1503).
- Fixed: Fixed how variable helpers are interpreted inside JSON logic conditions (Issue #1517).
- Fixed: "Query Posts" step has invalid settings validation, (Issue #1525)
- Fixed: Update ES-FR-IT translations (Issue #1477).
- Fixed: Remove email field from getAuthors REST API endpoint.
- Fixed: Convert workflow operations from GET to POST requests
- Fixed: Add capability checks for all workflow actions
- Fixed: Improve utils stripTags function
- Fixed: Implement backup structure validation to ensure data integrity
- Fixed: Add sanitization methods for workflows and settings data
- Fixed: Improve input validation with proper type checking and whitelisting
- Fixed: Enhanced workflow setDescription() method with wp_kses_post() sanitization
- Fixed: Enhance post expiration extraData data validation
- Fixed: Improve query parameter handling in settings controller
- Fixed: Enhance template rendering validation and path security
- Fixed: Add nonce verification to processMetaboxUpdate method
- Fixed: Improve input sanitization in debug log download
- Fixed: Add proper permission checks for post expiration data access
- Fixed: Add input sanitization for workflow node data values

[4.9.2] - 18 Novemeber, 2025

- Added: Add "User Role After Change" criteria to "User role is changed" trigger,  (Issue #1473).
- Changed: Re-organize Action Workflows quick edit links, (Issue #1479)
- Changed: Resize workflow editor expression box modal, (Issue #1480)
- Changed: Redirect users to "Action Workflows" screen on plugin activation, (Issue #1454)
- Changed: Add validation to workflow schedule custom date source variable, (Issue #1481)
- Fixed: Update ES-FR-IT translations (Issue #1477).
- Fixed: Enhance permission checks for REST API post modification endpoint, (Issue #1491).
- Fixed: Fixed duplicated constant values for hooks between modules and the core, (Issue #1292).

[4.9.1] - 27 October, 2025

- Added: Add "Run" and "Cancel" Bulk Edit in Scheduled Actions screen,  (Issue #1461).
- Added: Restore "User role change" trigger - PRO, (Issue #1212)
- Changed: Enable "Cancel Scheduled Actions" link for active workflow, (Issue #1455).
- Changed: Text changes for User role is changed, (Issue#1209)
- Fixed: DoAction step arguments not available as variables in subsequent workflow steps, (Issue #1467).
- Fixed: Custom i18n wrapper doesn't fall back to original string when translation is empty, (Issue #1465).
- Fixed: "No results found." message is breaking when using nl_NL, showing a JS error message, (Issue #1140).
- Fixed: TypeError: t.terms.forEach is not a function, (Issue #1169).

[4.9.0] - 14 October, 2025

- Added: Add a new trigger (When terms are added to a post), (Issue #1130).
- Added: Add Terms to the execution context, (Issue #1271).
- Added: Add new operator("has" & "does not have") to compare array in workflow editor filters, (Issue #1271).
- Added: Add "Duplicate" button for workflow editor filters, (Issue #1297).
- Added: Create the "Future Actions" workflows as samples in the "Action Workflows" area on install, (Issue #1309).
- Added "Change status to draft one week after publishing" workflow sample
- Added "Delete post one week after publishing" workflow sample
- Added "Remove all categories one week after publishing" workflow sample
- Added "Remove selected categories one week after publishing" workflow sample
- Added "Replace all categories one week after publishing" workflow sample
- Added "Stick post one week after publishing" workflow sample
- Added "Unstick post one week after publishing" workflow sample
- Changed: Make the "Send email" actions available in the Free version, (Issue #1430).
- Changed: Make the "Post status changed" actions available in the Free version, (Issue #1430).
- Changed: Make the "Post is published" trigger available in the Free version, (Issue #1452).
- Changed: Move items under workflow editor "Advanced" tab to "Actions" tab and remove "Advanced" tab, (Issue #1383).
- Changed: Change workflow editor edit icon {} to text, (Issue #1295).
- Fixed: Bulk Edit for Posts produces an empty Future box, (Issue #1302).
- Fixed: Newly created workflow "Manually run via Future Actions box" not working, (Issue #1425).
- Fixed: PHP message: PHP Fatal error: Uncaught ... NonexistentTermException in ...TermModel.php, (Issue #1442).
- Fixed: Issue with date timezones comparison in Future Actions, (Issue #1348).
- Fixed: Pro translations not working, (Issue #1444).
- Fixed: Update ES-FR-IT translations (Issues #1445, #1439).
- Removed: Remove the sidebar promo box, (Issue #1426).
- Developers: Refactor hardcoded do_action occurrences, (Issue #1335).
- Developers: Update the actions scheduler library, (Issue #726).
- Developers: Fixed workflow editor resolveExpressionsInJsonLogic forcing array into strings/json for all var.

[4.8.2] - 30 July, 2025

- Added: Add updated_post_meta and added_post_meta to core HooksAbstract,  (Issue #1416).
- Added: Add Pro nudge in Free version for Statuses and Metadata scheduling,  (Issue #1371).
- Changed: Hide options for "Automatically create actions" if disabled, (Issue #1398).
- Fixed: Mapped meta field for scheduled action not working for post added from the front end, (Issue #1418).

[4.8.1] - 17 July, 2025

- Fixed: "Manually run via Future Actions box" not working in Gutenberg Editor, (Issue #1405).
- Fixed: Conflict in WordPress 6.8.2 breaking post editor, (Issue #1404).
- Fixed: PHP message: PUBLISHPRESS FUTURE - Error registering classic editor metabox: Post is null or ID is not set, cannot load workflows" while reading response header from upstream, (Issue #1407).
- Fixed: Update pt-BR translations - PRO (Issue #1402).

[4.8.0] - 09 July, 2025

- Added: Add Key links on Plugins screen (Issue #1360).
- Added: Add new checkbox to hide specific fields in Metadata Mapping instead of the full metabox - PRO feature (Issue #1058).
- Changed: Changed the default value of "Workflow" field in the "Deactivate workflow for post" action to automatically select the first available workflow option (Issue #958).
- Changed: Improve consistency on the name of manually enabled triggers (Issue #1366).
- Changed: Conflict between the future action metabox and custom metadata when it comes from 3rd party plugins - PRO feature (Issue #1058).
- Changed: Update the field description Text on User interaction step (Issue #1384).
- Changed: Consistency with "Filters" name (Issue #1296).
- Changed: Workflow name consistency, update "Custom action" to "Do custom action" (Issue #1385).
- Fixed: Fixed WooCommerce Order Notice: Function ID was called incorrectly. Order properties should not be accessed directly (Issue #1388).
- Fixed: Plugin's text domain is loaded too early (Issue #1350).
- Fixed: Pro license is not activating - PRO (Issue #1397).
- Developers: Remove HooksAbstract::FILTER_ACTION_META_KEY filter application from PostModel

[4.7.1] - 11 June, 2025

- Fixed: Fixed Future Actions missing in post editor (Issue #1372).

[4.7.0] - 10 June, 2025

- Added: Add new workflow trigger "On custom action" that allows workflows to be triggered by custom WordPress action hooks, enabling integration with other plugins and custom code - PRO feature (Issue #1222).
- Added: Add new workflow step "Do action" that executes custom action hooks with arguments, enabling integration with other plugins and custom code - PRO feature (Issue #1222).
- Added: Add diagnostic check for Spatie Ray debugging tool in the Diagnostics and Tools settings tab, clarifying debugging capabilities.
- Added: Add a "Copy" button to workflows (Issue #1183).
- Added: Add a "Cancel Scheduled Actions" button to workflows lists (Issue #1326).
- Added: Add a new step for interactive delay that allows workflows to pause and wait for user interaction - PRO feature (Issue #1257).
- Added: Add new workflow engine action hooks for enhanced extensibility:
  - `publishpressfuture_workflow_engine_initialize_workflow`: Fires when a workflow is being initialized
  - `publishpressfuture_workflow_engine_setup_trigger`: Fires when configuring a workflow trigger
  - `publishpressfuture_workflow_engine_setup_step`: Fires when setting up a workflow step
  - `publishpressfuture_workflow_engine_execute_scheduled_step`: Fires when executing a scheduled workflow step
  - `publishpressfuture_workflow_engine_execute_step`: Fires when executing a workflow step
  - `publishpressfuture_workflow_engine_workflows_initialized`: Fires when all workflows are initialized
  - `publishpressfuture_workflow_engine_start_engine`: Fires when the workflow engine starts
  - `publishpressfuture_workflow_engine_run_workflows`: Fires when workflows begin execution
- Added: Add Trigger action `publishpressfuture_workflow_engine_execute_event_driven_step` when an event-driven step starts running - PRO feature.
- Added: Add new  "Duplicate Post" workflow action - PRO feature (Issue #1170).
- Added: Add a loco.xml file to support translation of the free version from within the Pro plugin using Loco Translate - PRO (Issue #1352).
- Added: Add a way to sort / filter / search the in-site notifications - PRO feature (Issue #1367).
- Changed: Stick and Unstick Post workflow steps can now be used anywhere in workflows, not just within Schedule branches (Issue #1204).
- Changed: Clarify the "Metadata" description by including table name for each metadata (Issue #1247).
- Changed: Stop automatic cancelation of scheduled actions when a workflow is disabled in support of manual button, (Issue #1326).
- Changed: Upgrade woocommerce/action-scheduler from 3.7.0 to 3.9.2, fixing PHP 8.4 compatibility.
- Changed: Consolidated JavaScript translations into the main .pot file and corresponding .po files, streamlining the translation workflow.
- Fixed: Settings Controller processes form submissions on every admin page load (Issue #1310).
- Fixed: Fixed validation issue in the workflow editor where selecting "Remove all terms" not removing required error (Issue #1244).
- Fixed: Fixed issue where Pro-only workflow triggers were incorrectly executing subsequent workflow steps in the free version of the plugin.
- Fixed: Fixed PHP compatibility by replacing arrow functions with anonymous functions for PHP 7.3 support.
- Fixed: Fixed PHP Warning: Trying to access array offset on null when opening new post, (Issue #1311).
- Fixed: Update pt-BR translations - PRO (Issue #1339).
- Fixed: Enhanced workflow auto-layout algorithm to prevent connection line crossings by implementing source handle-based ordering instead of creation order, improving visual clarity and readability of complex workflows.
- Removed: Remove site metadata from the execution context on workflows (Issue #1332).
- Developers: Remove unused InitineLoopPreventer trait from some classes, replacing it with the service "future.free/workflow-execution-safeguard".
- Developers: Add `convertDynamicHandlesToStatic` method to WorkflowModel for improved handle management in workflow processing.
- Developers: Enhanced workflow editor components with new InteractiveCustomOptions component for better option management.
- Developers: Update workflow editor CSS to increase max-width for react-flow nodes from 170px to 210px for better layout flexibility.
- Developers: Implement options validation in NodeValidator component to ensure workflow step configuration integrity.
- Developers: Enhanced workflow runner infinite loop prevention by implementing ExecutionContextInterface and adding execution ID tracking for improved detection accuracy.
- Developers: Implemented a unified i18n system for JavaScript translations that consolidates all script-specific translations into the main .pot files, streamlining the translation workflow.

[4.6.0] - 7 May, 2025

- Added: Added notification center icon to the admin topbar for in-site notifications - PRO feature (Issue #1290).
- Added: Added SendInSiteNotification step for in-site notifications - PRO (Issue #1290).
- Added: Added Scrollbar to Workflow Editor left sidebar (Issue #1281).
- Changed: Change Action Workflows Editor Modals "X" to "OK" and move the button to the bottom (Issue #1182).
- Changed: Move Metabox, Future Actions Column and Editor "Future Actions" fields from Display to New "Admin" tab (Issue #1215).
- Changed: Move Export and Import to first tabs in Settings (Issue #1213).
- Changed: Update Workflow Action "Update post" label and description (Issue #1283).
- Changed: Update Workflow Action "Post Name" to "Post Slug" (Issue #1282).
- Changed: Update Action Workflows post action and bulk edit messages (Issue #1219).
- Fixed: Fixed editor error when editing a reuseable block (Issue #1324).
- Fixed: Update ES, FR, and IT translations (Issue #1270).
- Fixed: Fixed REST API request detection to workflow engine execution environment identification (Issue #1290).
- Fixed: Fixed duplicate FILTER_REGISTER_REST_ROUTES constant (Issue #1290).
- Fixed: Fixed workflow editor filter area autocomplete dropdown overlapping content (Issue #1303).
- Fixed: Fixed PHP Warning: Trying to access array offset on null when opening new post, (Issue #1311).
- Fixed: Fixed DB tables that were not created after fresh install unless we visit the admin (Issue #1319).
- Fixed: Fixed support for caching during post insertion and status transition (Issue #1311).
- Removed: Remove the option to compact scheduled actions data (Issue #1233).
- Developers: Remove unused InitineLoopPreventer trait from some classes, replacing it with the service "future.free/workflow-execution-safeguard".
- Developers: Refactored workflow hooks replacing ACTION_ASYNC_EXECUTE_STEP with ACTION_SCHEDULED_STEP_EXECUTE for better semantic clarity.
- Developers: Added getId method to UserModel for retrieving user ID;
- Developers: Refactor WorkflowScheduledStepModel to simplify argument handling by removing compression logic and directly decoding uncompressed arguments.
- Developers: Replace methods ``getCachedPermalink` and `getCachedPosts` with a unified method: `getCacheForPostId` on the class `PostCache` and interface `PostCacheInterface`. Retrieves cached post and permalink data, including both postBefore and postAfter states.
- Developers: All triggers now emit the hook `publishpressfuture_workflow_trigger_executed` after execution.

[4.5.0] - 7 Apr, 2025

- Added: Added the `global.engine_execution_id` variable to the workflows.
- Added: Added the "After all repetitions" output branch to the "Schedule delay" step and "On schedule" trigger when repetition is enabled - PRO (Issue #1245).
- Added: Added the variables "repeat_count" and "repeat_limit" to the "Schedule delay" step - PRO.
- Added: Added more detailed debug messages when sending emails, helping to troubleshoot email sending errors (Issue #1232).
- Changed: Restored Post ID variables for post related triggers.
- Changed: Removed the `global.run_id` global variables and moved it to the workflow global variable as `global.workflow.execution_id`.
- Changed: Renamed workflow variable helpers, to workflow value processors.
- Changed: Changed default action unique ID by including the current timestamp, making it more unique by default.
- Changed: Changed the label "Next" to "At time" in the output of the "Schedule delay" step in the workflow editor.
- Changed: Removed not useful fields from the Quick Edit panel for Workflows: date, password, and others (Issue #1178).
- Changed: Allow editing custom post field selection expression for adding variable processor (e.g. date) and formatting the used value. Singlevariables expression builder is editable instead of readonly (Issue #1238).
- Changed: Implemented default sorting of scheduled actions by most recent first, providing better visibility of upcoming tasks (Issue #1242).
- Removed: Removed the "Allow duplicate scheduling" option in the Schedule delay step in the workflow editor. To prevent a duplicated action, specify a custom Unique Action Identified after enabling Advanced settings in the workflow editor.
- Fixed: Fixed false positive results for invalid JSON logic on post query input validation (Issue #1228).
- Fixed: Fixed scheduled delay tasks registration to not require a custom unique task identifier (Issue #1165).
- Fixed: Fixed detection of completed scheduled actions for single tasks, now properly allowing the same action to be scheduled multiple times (Issue #1165).
- Fixed: Fixed the action that unschedules completed recurring actions - PRO (Issue #1165).
- Fixed: Fixed issue with "On Schedule" trigger that was incorrectly scheduling recurring actions every few seconds instead of respecting the configured interval when repetition was enabled (Issue #1245).
- Fixed: Fixed incorrect execution count display in the Scheduled Actions page for repeating workflows that have a limit on number of executions (Issue #1249).
- Fixed: Improved text on the overdue action message in the posts list, removing red icon (#Issue 1193).
- Fixed: Fixed false positive error on step validation for steps connected to the Query Posts step, saying the variable "....posts" do not exists (Issue #1255).
- Fixed: Updated translations for ES, FR and IT languages (Issues #1256, #1225).
- Fixed: Fixed default workflows (samples), updating the trigger conditions for the new conditional query builder (Issue #1243).
- Fixed: Fixed uncaught exceptions adding error handling to some hook callbacks.
- Fixed: Fixed fatal error generated on posts lists when an invalid default future action date offset is configured for the post type (Issue #1224).
- Fixed: Fixed wrong repetition inverval for the "On schedule" trigger (Issue #1259).
- Fixed: Fixed wrong error message on database schema check when an index is missed (Issue #1236).
- Fixed: Fixed the display of scheduled actions for posts when workflows are manually enabled using the checkbox (Issue #1230).
- Fixed: Fixed error message "Schedule step is required for this workflow" on any repeating scheduled step in the Scheduled Actions list (Issue #1229).
- Fixed: Fixed step validation error message about the field "Post" containing an invalid variable (Issue #1210).
- Fixed: Fixed wrong redirection after selecting custom number of debug logs to display (Issue #1264).
- Developers: Added new method `isLogic` to `JsonLogicEngineInterface`.
- Developers: Removed arguments from `compact` and `getVariable` methods on `PostMetaResolver` class.
- Developers: Added new method `getWorkflowEngine` to the interface `StepProcessorInterface`.
- Developers: Added new method `getWorkflowExecutionId` to the interface `StepProcessorInterface`.
- Developers: Removed the service `WORKFLOW_VARIABLES_HANDLER`, replacing it with the `WORKFLOW_VARIABLES_HANDLER_FACTORY`.
- Developers: Removed the method `getVariablesHandler` from the `WorkflowEngine` class.
- Developers: Renamed "Runtime Variables Handler" to "Workflow Execution Context".
- Developers: Added new columns to the table `_ppfuture_workflow_scheduled_steps`: `post_id` and `repetition_number`.

[4.4.0] - 13 Mar, 2025

- Added: Added new action: Update Post - PRO (Issue #1143).
- Added: Added new trigger: Manual run via posts row action - PRO (Issue #1168).
- Added: Added new trigger: Post Author Changed - PRO (Issue #1144).
- Added: Added support for "date" helper on runtime variables in the workflow editor (Issue #1160).
- Added: Added step slug/name to the top of each step node in the workflow editor.
- Added: Added form to customize workflow and settings import from JSON files (Issue #1152).
- Added: Added new field in post query step settings to query posts by author.
- Added: Added new field in post query step settings to query posts by terms.
- Added: Added time selection to the Schedule step (Issue #1124).
- Added: Added new filter `publishpressfuture_workflow_route_variable` to customize variable names in workflow runtime (Issue #1126).
- Added: Added posts query builder to post-related triggers (Issue #1131).
- Added: Added "update" variable to the Post is Saved step (Issue #1147).
- Added: Added validation to prevent empty placeholders in expression builder.
- Added: Added validation to prevent unclosed placeholders in expression builder.
- Added: Added loading message during workflow load.
- Changed: Moved Export / Import tabs to Settings page and removed respective admin menu (Issue #1127).
- Changed: Changed description field in workflow steps to use a popover within the inspector card, saving sidebar space.
- Changed: Removed left padding from workflow step details panel.
- Changed: Removed attributes table from inspector card in developer mode (data still visible in Developer Info panel)
- Changed: Improved debug panel in workflow editor by separating node data and settings into distinct items.
- Changed: Renamed `global.trace` variable label to "Workflow Step Trace" (Issue #1126).
- Changed: Renamed `global.execution_id` to `global.run_id` and its label to "Workflow Run ID" (Issue #1126).
- Changed: Removed the "Task Execution Order" field from Schedule Delay step settings (Issue #1180).
- Changed: Renamed "Auto-enable" setting to "Automatically create actions" for clarity (Issue #1157).
- Changed: Updated text in Permissions settings for better clarity (Issue #1136).
- Changed: Renamed "postId" property to "post_id" in node data type variables for consistency (with backward compatibility).
- Changed: Replaced Post Query fields with a query builder for post-related triggers and actions (Issue #1131).
- Changed: Changed post type selection in Settings page from tabs to a select box (Issue #1188).
- Changed: Added selected post type name as title in Post Types settings (Issue #1191).
- Changed: Added the step's name to the list of variables, distinguishing among similar variables (Issue #1205).
- Changed: Sorted the list of variables moving less important variables to the bottom of the list (Issue #1207).
- Changed: Changed the description of the "Is Update" variable in the "Post is saved" trigger (Issue #1206).
- Changed: Updated the text of the promobox highlighting workflow editor features (Issue #1164).
- Fixed: Fixed fatal error when selecting multiple steps or connections in workflow editor (Issue #1162).
- Fixed: Fixed default data in "Send Ray" step to send all input values instead of blank message.
- Fixed: Fixed "Restore" and "Delete Permanently" actions for trashed workflows (Issue #1175).
- Fixed: Fixed node validation rules for variables (Issue #1177).
- Fixed: Fixed scroll behavior in variables list within query builder.
- Fixed: Fixed column height in variables list within query builder.
- Fixed: Fixed the top header in the right sidebar, hiding it (Issue #1195).
- Fixed: Fixed the expressions validation in the workflow editor for "Send to Ray" step, accepting the `{{input}}` expression (Issue #1197).
- Fixed: Fixed the order post related trigger activation to correctly retrieve posts before and after state, and making sure post meta is saved.
- Fixed: Fixed the workflow step execution avoiding duplicate processing of post related triggers, adding a threshold time of 2 seconds.
- Fixed: Fixed messages displayed after manually running scheduled actions (Issue #1202).
- Developers: Added new method `resolveExpressionsInArray` to `RuntimeVariablesHandler` class.
- Developers: Added new method `resolveExpressionsInText` to `RuntimeVariablesHandler` class.
- Developers: Added new method `extractExpressionsFromText` to `RuntimeVariablesHandler` class.
- Developers: Deprecated methods `replacePlaceholdersInText` and `extractPlaceholdersFromText` in favor of new methods `resolveExpressionsInText` and `extractExpressionsFromText` in `RuntimeVariablesHandlerInterface` and `RuntimeVariablesHandler` class.
- Developers: Added new data field to workflow editor for querying users by role and ID.
- Developers: Added new model for user roles.
- Developers: Refactored data types schema: renamed "type" to "primitiveType" and added "itemsType" to array.
- Developers: Added new data types: post_status, post_type, url, user_roles, meta.
- Developers: Refactored workflow editor utility functions for clearer naming.
- Developers: Added step-scoped variables definition for configuring step runner behaviors in editor.
- Developers: Added new filter `publishpressfuture_future_actions_tabs` for filtering future actions admin page tabs.
- Developers: Deprecated the `InfinityLoopPreventer` trait.
- Developers: Added `WorkflowExecutionSafeguard` service to centralize infinite loop and duplicate execution prevention.
- Developers: Introduced priority property to variables, allowing to sort the variables list according to importance.

[4.3.3] - 03 Feb, 2025

- Fixed: Fix the overdue actions check in the Scheduled Actions list screen (Issue #1155).
- Fixed: Update translations (Issue #1156).


[4.3.2] - 30 Jan, 2025

- Fixed: Fix typo in the `WorkflowEngine` class.
- Fixed: Fix reference to deprecated classes and interfaces.
- Fixed: Fix translation of shortcode settings in the Display settings page.

[4.3.1] - 30 Jan, 2025

- Added: Add new option to the Schedule workflow step to select the behavior when a duplicate scheduled action is found (Issue #956).
- Added: Add daily check and notification for past-due actions, with settings to enable/disable and customize the email addresses (Issue #229).
- Added: Add check for overdue actions in the Scheduled Actions list screen (Issue #232).
- Added: Add new validation rule to check if the expression is valid in the workflow editor (Issue #742).
- Added: Add new validation rule to check if the value of a field has invalid variable references (Issue #969).
- Changed: Change the workflow step custom label to be a step description and still display the original step label (Issue #1114).
- Changed: Changed text and description of fields in the Settings page (Issues #1097, #1103, #1104, #1105).
- Changed: Changed the field description in the Post Query step (Issue #1100).
- Changed: Changed the label of the "Schedule" workflow step to "Schedule delay" (Issue #1122).
- Changed: Changed the label of the "On Cron Schedule" trigger to "On schedule" (Issue #1122).
- Changed: Changed the label of the "Conditional split" workflow step to "Conditional" (Issue #1117).
- Changed: Changed the color of the "False" branch in the "Conditional" workflow step to a slightly darker color.
- Changed: Changed the "Not" field in the "Conditional" workflow step to only be displayed when there are rules (Issue #1118).
- Changed: Changed the description of the "Conditional" workflow step conditions modal (Issue #1118).
- Changed: Changed the validation rule message of the "Stick" and "Unstick" workflow steps (Issue #1101).
- Changed: Changed the message in the Scheduled Actions list screen when a scheduled action is missing its original Schedule step (Issue #971).
- Changed: Removed the "Single variable mode" from the text in the expression builder (Issue #1118).
- Changed: Automatically select post-related settings and defaults in workflow steps that interact with posts (Issue #969).
- Changed: Removed the screenshot feature from the workflow editor (Issue #1135).
- Changed: Changed the label and description of some workflow steps for making it more intuitive (Issue #1101).
- Changed: Changed the default duplicate handling on workflow stepsto "Replace existing task" (Issue #956).
- Changed: Step "Ray - Debug" renamed to "Send to Ray" (Issue #1143).
- Changed: Step "Debug Log" renamed to "Append to debug log" (Issue #1143).
- Changed: Step "Conditional" renamed to "Conditional Delay".
- Changed: Changed the default step's slug to reflect the new step name and classes.
- Changed: Changed the Schedule Delay step settings to be more intuitive.
- Changed: Changed some text in the workflow editor to be more user friendly.
- Fixed: Fix SQL syntax error in MariaDB lower than 11.6 when deleting orphan scheduled steps (Issue #1087).
- Fixed: Update translations (Issue #1113).
- Fixed: Fix extra line (empty value character) on some post in the future action column (Issue #1106).
- Fixed: Fix error when the step being executed is not found (Issue #1123).
- Fixed: Fix the space on right margin of the workflow editor nodes.
- Fixed: Fix queries in the `ScheduledActionsModel` to use the group ID.
- Fixed: Fix infinite loop detection in post related triggers when fired by a bulk edit action (Issue #943).
- Fixed: Fix space on the outputs of the workflow steps in the Scheduled Actions list screen.
- Fixed: Fix performance issue when validating the workflow editor nodes (Issue #1137).
- Fixed: Fix the constructor of some workflow triggers (Issue 1141).
- Fixed: Fix the error related to wrong arguments passed to sprintf on nl_NL language (Issue #1138).
- Fixed: Fix the JS error when the expression builder is opened with an expression containing only numbers (Issue #1142).
- Fixed: Fix specific text stripping tags from translated string.
- Developers: Refactor the method `deleteExpiredScheduledSteps` in the class `ScheduledActionsModel` renaming it to `deleteExpiredDoneActions`.
- Developers: Add new method `getExpiredPendingActions` to the class `ScheduledActionsModel`.
- Developers: Deprecated the method `isInfinityLoopDetected` in the trait `InfiniteLoopPreventer` and use the method `isInfiniteLoopDetected` instead.
- Developers: Add new argument `$uniqueId` to the method `isInfiniteLoopDetected` in the trait `InfiniteLoopPreventer` (Issue #943).
- Developers: Remove the methods `convertLegacyScreenshots`, `setScreenshotFromBase64`, `setScreenshotFromFile` and `getScreenshotUrl` from the class `WorkflowModel` (Issue #1135).
- Developers: Remove the methods `convertLegacyScreenshots`, `setScreenshotFromBase64`, `setScreenshotFromFile` and `getScreenshotUrl` from the interface `WorkflowModelInterface` (Issue #1135).
- Developers: Remove the methods `getWorkflowScreenshotStatus`, and `setWorkflowScreenshotStatus` from the class `SettingsFacade` (Issue #1135).
- Developers: Refactored step types and step runners moving files to new folder structure (Issue #1143).
- Developers: Refactored most of the code renaming "Node" to "Step", "NodeRunner" to "StepRunner", and so on (Issue #1148).

[4.3.0] - 08 Jan, 2025

- Added: Add new variables selector and an expression builder (Issue #976).
- Added: Add support to metadata in the variables resolvers and post type variables (Issue #1069, #939).
- Added: Add the site ID to the site data type schema.
- Added: Add the post author property to the post data type schema in the workflow editor (Issue #947).
- Added: Add the post slug property to the post data type schema in the workflow editor.
- Added: Add new trigger: Post is Published - PRO (Issue #944).
- Added: Add new trigger: Post Status Changes - PRO (Issue #945).
- Added: Add new trigger: Post is Scheduled - PRO (Issue #946).
- Added: Add new trigger: Post Meta Changed - PRO (Issue #1059).
- Added: Add new action: Post Meta Add - PRO (Issue #732).
- Added: Add new action: Post Meta Delete - PRO (Issue #732).
- Added: Add new action: Post Meta Update - PRO (Issue #732).
- Added: Add the option to change manually enabled workflows in the bulk edit screen (Issue #942).
- Added: Add the "Save as current status" shortcut to the workflow editor (CTRL/CMD + S) (Issue #1084).
- Added: Add new display settings to customize the shortcode output (Issue #203).
- Added: Add new step setting field to customize the step label in the workflow editor (Issue #1090).
- Added: Add Future Action data support in the workflow editor, allowing to reference future actions in expressions (Issue #948).
- Changed: Replace text fields and input/variables selectors on step settings with the new expression builder (Issue #976).
- Changed: Changed the border of selected steps to dashed line.
- Changed: Moved the panel "Step Data Flow" to the developer mode.
- Changed: Removed the arrow indicator from the workflow title and added a new Status column to the workflows list screen (Issue #970).
- Changed: Post's variable resolver now also accept a property without `post_` prefix.
- Changed: User's variable resolver now also accept a property without `user_` prefix.
- Changed: Changed the options in the "Debug Data" field to be more intuitive allowing a custom data expression to be selected.
- Changed: The conditional step now uses the new expression builder.
- Changed: Improved the UI in the conditional step settings.
- Changed: Added field descriptions to the post query step settings panel (Issue #1081).
- Changed: Only display the bulk edit option "Update Future Action from Post Metadata" if feature is enabled (Issue #622).
- Changed: Updated language files.
- Changed: Remove focus from the toolbar Delete button when workflow step is selected (Issue #1083).
- Changed: Improved the text in the variables selector modal.
- Fixed: Fix error when the date or time format is empty in the settings page (Issue #212).
- Fixed: Fix empty title and label in the future action panel when custom title and label are not set (Issue #1075).
- Fixed: Fix the width of the checkbox in the future action panel (#1076).
- Fixed: Fix the permalink in the Post Updated trigger for the post before variable.
- Fixed: Fix the variable names in the "Add extra terms to post" step (Issue #1079).
- Fixed: Fix the validation message for the recipient field in the Send Email step (Issue #1078).
- Fixed: Fix the date format in the shortcode.
- Fixed: Fix loading a workflow that doesn't have a specific step type (Issue #883).
- Fixed: Fix the first save of a workflow to transit from auto-saved to draft (Issue #1086).
- Fixed: Fix warning about deprecated jQuery click() method in the workflow editor.
- Fixed: Fix the auto-layout algorithm to avoid overlapping edges and correctly dimension each node and spacing between nodes (Issue #1102).
- Fixed: Fix the warning about deprecated method `next` in the class `ActionScheduler_Schedule` (Issue #1107).
- Developers: Deprecated the method `get_wp_date` in the class `PostExpirator_Util` and use the method `getWpDate` from the class `PublishPress\Future\Framework\WordPress\Facade\DateTimeFacade instead.
- Developers: Deprecated the method `wp_timezone_string` in the class `PostExpirator_Util` and use the method `getTimezoneString` from the class `PublishPress\Future\Framework\System\DateTimeHandler` instead.
- Developers: Deprecated the method `get_timezone_offset` in the class `PostExpirator_Util`.
- Developers: Deprecated the method `sanitize_array_of_integers` in the class `PostExpirator_Util`.
- Developers: Add new param $metaValue to the method `deleteMeta` in the class `PublishPress\Future\Framework\WordPress\Models\PostModel`.
- Developers: Remove the `steps` property from the workflow data type schema.

[4.2.0] - 09 Dec, 2024

- Added: Add new admin page to export and import workflows and plugin settings (Issue #704).
- Added: Add global variable `global.execution_id` to the workflow engine to identify the current execution of the workflow.
- Added: Add setting fields to customize the metabox title and checkbox label (Issue #227).
- Added: Add method `disableExpiration` to the class `PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel`.
- Added: Add `*` to indicate required fields in the workflow editor (Issue #974).
- Added: Add new setting to disable/enable the screenshot feature in the workflow editor (Issue #1066).
- Added: Add new Custom Data option to "Ray - Debug step" to debug custom expressions on workflows (Issue #1067).
- Added: Add support to metadata when evaluating expressions in a workflow. Post, site, user and workflow (post) metadata are now available when evaluating expressions (Issue #1069).
- Added: Add support to custom email addresses using the post metadata when sending emails in a workflow (Issue #939).
- Changed: Move notification settings to a specific tab (Issue #190).
- Changed: Disable the workflow screenshot feature by default (Issue #1066).
- Changed: Changed the Message field in the "Log - Add" step displaying a textarea instead of a text field (Issue #1068).
- Changed: Changed the Custom Email Addresses field in the Send Email step to be a textarea (Issue #939).
- Changed: Changed the Subject field in the Send Email step to be a textarea (Issue #939).
- Changed: Set the default value of Email Recipient on Send Email step to Site Admin (Issue #1071).
- Fixed: Do not remove expiration post meta when clearing the scheduled action (Issue #1053).
- Fixed: Fix DB error when deleting orphan scheduled steps (Issue #1060).
- Fixed: Potential fix for DOM text reinterpretation as HTML issue.
- Fixed: Fix error when a trigger node type is not found.
- Fixed: Fix warning PHP Deprecated:  ltrim(): Passing null to parameter #1 ($string) of type string on the Scheduled Actions table.
- Fixed: Fix error on table ScheduledActionsTable refactoring calls to `next` instead of `get_date`.
- Fixed: Fix displaced labels for checkboxes in the Future Actions metabox and manual workflow activation checkbox (Issue #1057).
- Fixed: Fix translations for user roles in the plugin settings page (Issue #1050).
- Fixed: Fix error on Post Status filter in the Post Updated trigger (Issue #1074).

[4.1.3] - 22 Nov, 2024

- Added: check for the constant `PUBLISHPRESS_FUTURE_FORCE_DEBUG` to force debug mode.

- Fixed: error on fresh install about missing table (Issue #1051).

[4.1.2] - 21 Nov, 2024

- Fixed: translations (Issues #1003, #1006, #1007, #1026).
- Fixed: pt-BR translations (Issue #10018).
- Fixed: es, it, fr translations (Issue #1047).
- Fixed: zombie auto-drafts appearing in the future when auto-enable is activated (Issue #1024).
- Fixed: call to undefined function `error_log` (Issue #1036).
- Fixed: the page title in the workflow editor (Issue #1027).
- Fixed: the page title on admin pages of 3rd party plugins (Issue #1037).
- Fixed: the pt-BR translations.
- Fixed: the size of Pro badge on step inserter in the workflow editor.

[4.1.1] - 12 Nov, 2024

- Fixed: the layout of inserter in the workflow editor for WP 6.7 (Issue #1025).
- Fixed: the layout of the top toolbar in the workflow editor for WP 6.7 (Issue #1028).

- Changed: Minimum required version of WordPress is now 6.7.
- Changed: Minimum required version of PHP is now 7.4.

[4.1.0] - 11 Nov, 2024

- Added: more detailed debug logs to the workflow engine (Issue #724).
- Added: button to copy the debug logs to the clipboard (Issue #724).
- Added: "Published" status to the legacy expiration statuses (Issue #1023).
- Added: new workflow step to write a log message (Issue #690).

- Fixed: the timezone in the default date applied from default action time (Issue #1005).
- Fixed: the timezone in the date preview (Issue #1004).

- Changed: the debug log viewer adding text to a textarea (Issue #724).
- Changed: the debug log viewer adding a button to download the entire log or copy it to the clipboard (Issue #724).
- Developers: The debug log viewer now automatically scrolls to the bottom when the page loads (Issue #724).
- Deprecated: the class `PublishPress\Future\Modules\Debug\Debug` and use the logger facade instead.
- Changed: Better handling of the exceptions and errors thrown by the plugin.
- Changed: the admin submenu item "Scheduled Actions" and added a button in the workflows list screen (Issue #1022).
- Changed: the "post-expirator-debug.php" file which is no longer used.

- Added: new class `PublishPress\Future\Framework\System\DateTimeHandler` to handle date and time operations.
- Changed: the REST API `/settings/validate-expire-offset` endpoint return value renaming `preview` to `calculatedTime`.
- Changed: the REST API `/settings/validate-expire-offset` endpoint to log an error message when the offset is invalid.
- Added: `DateTimeHandlerInterface` as dependency to the class `PublishPress\Future\Modules\Expirator\Models\PostTypeDefaultDataModel`.
- Added: `LoggerInterface` as dependency to the class `PublishPress\Future\Modules\Expirator\Module`.
- Added: `DateTimeHandlerInterface` as dependency to the class `PublishPress\Future\Modules\Expirator\Module`.
- Deprecated: the constant `PublishPress\Future\Core::ACTION_ADMIN_ENQUEUE_SCRIPT` in favor of `PublishPress\Future\Core::ACTION_ADMIN_ENQUEUE_SCRIPTS`.
- Removed: the action `publishpressfuture_workflow_engine_running_step` from the workflow engine.
- Added: new methods to the class `PublishPress\Future\Framework\Logger\Logger` to retrieve the log count, the log size, and to fetch the latest logs.
- Developers: Node runner processors now accept a branch argument to get the next steps and run the next steps.

[4.0.4] - 24 Oct, 2024

- Fixed: the workflows list screen to be shown only to users with `manage_options` capability (Issue #998).
- Fixed: compatibility with the "WP Remote User Sync" plugin (Issue #999).

[4.0.3] - 22 Oct, 2024

- Added: the banner notice to the workflows list screen.

- Fixed: PHP warning when post attribute is empty in the workflow model (Issue #987, #988).
- Fixed: error when`manage_posts_columns` filter do not receive a post type (Issue #990).
- Fixed: error about undefined index: date (Issue #991).

[4.0.2] - 21 Oct, 2024

- Fixed: error when the filter `the_title` is called without an ID (Issue #984).

[4.0.1] - 21 Oct, 2024

- Fixed: the database schema check for version 4.0.0 on fresh installations, (Issue #928).

[4.0.0] - 01 Oct, 2024

- Added: the Workflows feature, with the workflow editor and the workflow engine.

- Changed: post model to update post date when setting post status to publish.
- Fixed: error when the current_post->ID is empty for unknown reasons, usually related to 3rd party plugins.

- Developers: The list of scheduled actions now displays the repetition count/date limits (#928).
- Fixed: Update language files.

- Developers: Interface `PublishPress\Future\Core\HookableInterface`: Add new method `removeFilter` to remove a hooked filter.
- Developers: Interface `PublishPress\Future\Core\HookableInterface`: Add new method `removeAction` to remove a hooked action.
- Developers: Class `PublishPress\Future\Framework\WordPress\Facade\HooksFacade`: Add new method `removeFilter` to remove a hooked filter.
- Developers: Class `PublishPress\Future\Framework\WordPress\Facade\HooksFacade`: Add new method `removeAction` to remove a hooked action.
- Developers: New method to publish posts using the class PublishPress\Future\Framework\WordPress\Models\PostModel.
- Developers: Add new filter 'publishpressfuture_migrations' to filter the list of migrations that will be executed.
- Developers: Call the action 'publishpressfuture_fix_db_schema' when a DB fix is executed from the settings page.
- Developers: Call the action 'publishpressfuture_upgrade_plugin' when the plugin is upgraded.
- Changed: the data type from void to int for the method 'PublishPress\Future\Modules\Expirator\Interfaces]CronInterfac::scheduleRecurringAction'.
- Changed: the data type from void to int for the method 'PublishPress\Future\Modules\Expirator\Interfaces]CronInterfac::scheduleAsyncAction'.
- Developers: Add new filter 'publishpressfuture_schema_is_healthy' to check if the DB schema is healthy.
- Developers: The method 'PublishPress\Future\Modules\Workflows\Models\WorkflowModel::getStepFromRoutineTreeRecursively' now always returns an array.
- Developers: Add new filter 'action_scheduler_list_table_column_recurrence' to filter the recurrence column in the scheduled actions list.
- Developers: Add new method 'getNodeById' to the class 'PublishPress\Future\Modules\Workflows\Models\WorkflowModel'.

[3.4.4] - 21 Aug, 2024

- Fixed: Improve notice message when scheduled action runs after pressing "run" (PR #896).
- Fixed: Fixed support for the Event Espresso plugin (PR #900).
- Fixed: React warning about createRoot being deprecated.
- Fixed: Fixed empty fieldset displayed when the bos is disabled for the post type (Issue #792).
- Fixed: Update language files.

- Added: tabs for post types in the post types settings page (PR #895).

- Added: computed date preview to the general settings page (PR #897).
- Added: option to hide the calendar by default in the future action panel (PR #899).
- Added: new filter `publishpressfuture_posts_future_action_column_output` to the Future Action column.

[3.4.3] - 06 Aug, 2024

- Removed: icon from the Future metabox in the block editor, #821

- Fixed: Update translation files
- Fixed: Only load the quick-edit script if in the post list screen
- Fixed: quick edit action box to use the filter to hide action box when deactivated for the post type, #884
- Fixed: the database schema check to also check the debug log table, #887
- Fixed: the database schema check to check the table indexes, #887

[3.4.2] - 15 Jul, 2024

- Added: the current date and time to date preview in the date/time offset setting field, #840

- Changed: Optimized the date/time offset validation requests in the Post Types settings, #840
- Fixed: error message in the date/time offset setting field, #841
- Fixed: user capabilities check in the block editor, #727
- Fixed: Update ES, FR, and IT translations, #859

- Changed: the text in the promo screen for the Actions Workflow feature, #867

[3.4.1] - 02 Jul, 2024

- Developers: add promo screen for Actions Workflows, #777
- Developers: the post_id attribute to the futureaction shortcode, #814

- Fixed: some translations in ES, FR, and IT languages, #798
- Fixed: "no future actions" message in the scheduled actions list, #788
- Fixed: avoid fatal error for wrong argument counting
- Fixed: issues pointed by PHPCS
- Fixed: an exception message

- Changed: language files
- Changed: the exception message when the date/time offset is invalid
- Changed: composer files for dev dependencies

[3.4.0.1] - 20 Jun, 2024

- Fixed: fatal error for low level users when PublishPress menu is not available, #803
- Fixed: wrong action date on the future action panel, #802

- Developers: The interface `PublishPress\Future\Modules\Expirator\Interfaces\ActionArgsModelInterface` has changed:
- Changed: - Method `setCronActionId` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setPostId` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setArgs` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setArg` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setCreatedAt` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setEnabled` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setScheduledDate` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setScheduledDateFromISO8601` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `setScheduledDateFromUnixTime` now returns void instead of `ActionArgsModelInterface`
- Changed: - Method `convertUnixTimeDateToISO8601` is now public
- Changed: - Method `convertISO8601DateToUnixTime` is now public
- Changed: exception message when the date/time offset is invalid

[3.4.0] - 20 Jun, 2024

- Added: In the JS context, implemented a way to extend the future action panel using SlotFill `FutureActionPanelAfterActionField` and setting extra fields to the panel, right after the action field
- Added: a new filter to allow filtering the options of the future action being scheduled: `publishpressfuture_prepare_post_expiration_opts`
- Added: method `scheduleRecurringAction` to the `CronToWooActionSchedulerAdapter` to schedule recurring action
- Added: method `scheduleAsyncAction` to the `CronToWooActionSchedulerAdapter` to schedule async action
- Added: In the JS context, added the slot `FutureActionPanelTop` to the beginning of the future panel

- Added: `$unique` and `$priority` arguments to the `scheduleSingleAction` method in the `CronToWooActionSchedulerAdapter` class
- Developers: Method `scheduleRecurringAction` renamed to `scheduleRecurringActionInSeconds` in the `CronToWooActionSchedulerAdapter` class
- Added: argument `$clearOnlyPendingActions` to the method signature `clearScheduledAction` to the `CronInterface` interface
- Changed: the method `clearScheduledAction` in the class `CronToWooActionSchedulerAdapter` adding new argument `$clearOnlyPendingActions`, allowing to remove running actions
- Developers: The plugin activation and deactivation callback functions were moved from the main file to independent files
- Changed: the admin menu names for clarity
- Changed: the promo sidebar for mentioning the Actions Workflow feature

- Fixed: error when quick-edit data is not available, #730
- Fixed: dependency of the enqueued scripts for the future action box. Add 'wp-i18n', 'wp-components', 'wp-url', 'wp-data', 'wp-api-fetch', 'wp-element', 'inline-edit-post', 'wp-html-entities', 'wp-plugins' as dependencies
- Fixed: Updated ES, FR and IT translations, #698
- Fixed: Redirects to the settings page after activating the plugin, #764
- Fixed: access to the View Debug settings tab when debug is disabled
- Fixed: the position of the "Upgrade to Pro" and "Settings" menu items in the admin bar

[3.3.1] - 19 Mar, 2024

- Added: validation for the date and time offset in the settings page, #683
- Added: form validation to the settings panel
- Added: form validation to the metabox panel
- Added: a date preview to the date/time offset setting field

- Changed: The actions to move posts to another status where grouped in a single action, with a dropdown to select the status, #668
- Changed: The actions "draft", "private" and "trash" are deprecated in favor of "change-status", #668
- Changed: The metadata hash key has now a prefix "_" marking it as a private key, #695
- Changed: the name of some actions
- Changed: the label of the field to select terms when "Replace all terms" is selected, #664

- Fixed: Make it impossible to choose dates in the past, #443
- Fixed: Enter key submits quick-edit panel when selecting a taxonomy term, #586
- Fixed: The name of the taxonomy in the actions field is now updated in the settings panel when the taxonomy is changed, #676
- Fixed: Possible to add an action using an empty category setting, #587
- Fixed: language files for ES, IT, FR, #685
- Fixed: inconsistent text in the filter for "Pending" actions, #673
- Fixed: Improve the message when no actions are found: "No Future Actions", #666

[3.3.0] - 28 Feb, 2024

- Added: new filter for filtering the list of post types supported by the plugin: `publishpressfuture_supported_post_types`, #677
- Added: new filter for choosing to hide or not the Future Action in the post editors: `publishpressfuture_hide_metabox`, #69
- Added: new filter for filtering the post metakeys in the post model: `publishpressfuture_action_meta_key`, #69
- Added: new method `medataExists` to the `PublishPress\Future\Framework\WordPress\Models\PostModel` class
- Added: support to a hash in the post meta `pp_future_metadata_hash`, to identify if the future action's post meta has changed or was scheduled by metadata (fully available only on PRO)

- Deprecated: the filter `postexpirator_unset_post_types` in favor of the new filter `publishpressfuture_supported_post_types`, allowing not only removal but addition of new post types to the list of supported post types, #677
- Changed: The list of post types in the settings page now also shows the non-public post types that are not built-in on WordPress, #677
- Removed: the X and Facebook icons from the footer in the admin pages, #667
- Changed: Updated the URLs on the plugin's footer, #667
- Changed: Minor change in the description of the setting that controls the activation/deactivation future action for the post type
- Changed: The metadata `_expiration-date-status` now can be specified as `1` or `'1'` and not only `'saved'`, #69
- Changed: The action `publishpress_future/run_workflow` is now deprecated in favor of `publishpressfuture_run_workflow`

- Fixed: language files for ES, IT, FR, #665
- Fixed: error when a term does not exist, #675
- Added: new interface for NoticeFacade: NoticeInterface

- Removed: the legacy action `postExpiratorExpire`. This action will not trigger the future actions anymore
- Removed: the legacy action `publishpressfuture_expire`. This action will not trigger the future actions anymore

[3.2.0] - 25 Jan, 2024

- Added: the possibility to use non-hierarchical taxonomies, #285
- Added: new future action to remove all taxonomy terms of a post, #652
- Added: new action hook `publishpressfuture_saved_all_post_types_settings` to allow developers to trigger an action when the Post Types settings are saved

- Deprecated: the constant `PublishPress\Future\Modules\Settings\SettingsFacade::DEFAULT_CUSTOM_DATE` and replaced it with `::DEFAULT_CUSTOM_DATE_OFFSET`
- Changed: the date and time format settings fields to the Display tab, #605
- Added: description to the taxonomy setting field in the Post Types tab, #641
- Changed: the Post Types settings tab to the first position, #619
- Changed: Simplify the name of actions on taxonomy-related actions, adding the actual name of the taxonomy, #294
- Changed: the text on the Status column in the Future Actions list, from "Pending" to "Scheduled", #661
- Fixed: typos and improved the text in the Post Types settings tab, #659
- Developers: The list of supported post types in the settings page only shows public post types, and non-public that are built-in and show the UI

- Fixed: consistency on radio buttons alignment on the settings page
- Fixed: Hides the legacy cron event field from Diagnostics and Tools settings tab if no legacy cron event is found
- Fixed: the "Change Status to Trash action" on custom post types, #655
- Added: back support for reusable blocks, #200
- Fixed: the language files, #653
- Fixed: error 404 when activating future action on a post type that has no taxonomy registered, #662

[3.1.7] - 04 Jan, 2024

- Fixed: compatibility with plugins like "Hide Categories and Products for WooCommerce", making sure terms are not hidden in the taxonomy field, #639
- Fixed: the terms select field in the settings page, expanding it on focus, #638
- Fixed: the fatal error when hook `add_meta_boxes` didn't receive a `WP_Post` instance as parameter, #640
- Fixed: issue with the "NaN" categories in the classic editor, #647
- Fixed: issue with accents on the taxonomy field in the settings, #642

[3.1.6] - 20 Dec, 2023

- Added: a new setting to select the time format in the date picker component, #626

- Changed: Stick the library woocommerce/action-scheduler on version 3.7.0, so we don't force WP min to 6.2
- Changed: Min WP version is now 6.1, #627
- Developers: The field to select terms now expands when the user focus on it, not requiring to type a search text, #633
- Changed: Increase the limit of items displayed in the field to select terms. It shows up to 1000 items now, #633

- Fixed: support for WP between 6.1 and 6.4, #625
- Fixed: the search of posts in the posts lists, #620
- Fixed: classic meta box when using Classic Editor plugin with the classic editor as default, #624
- Fixed: default date for new posts, #623
- Fixed: the quick edit form and future action column for pages, #618
- Fixed: support to custom taxonomies that are not shown in the Rest API, #629

[3.1.5] - 14 Dec, 2023

- Fixed: array_map(): Argument must be of type array, string given, #606
- Removed: broken and invalid setting to use classic metabox, #604
- Fixed: a PHP warning in the posts screen if the selected term does not exist anymore, #612
- Changed: the ES, IT and FR translations, #609

[3.1.4] - 13 Dec, 2023

- Added: Taxonomy term field now supports adding a new term by typing a new value
- Added: a button to toggle the calendar on the future action panels. Quick/Bulk edit are collapsed by default, #583
- Added: Display the taxonomy name in the future action panels instead of showing "Taxonomy", #584

- Developers: all the future action panels to use the same React components, fixing the inconsistency between the panels, #572
- Changed: external dependency of the React Select library, using now the WordPress internal library
- Changed: In the Action field on Post Type settings, the taxonomy-related actions are only displayed if the post type has any term registered
- Changed: the order of fields in the future action panels, moving action and taxonomy to the beginning
- Developers: The method `ExpirationScheduler::schedule` now automatically converts the date to UTC before scheduling the action
- Developers: The action `publishpressfuture_schedule_expiration` now receives the date in the local site timezone
- Changed: the library woocommerce/action-scheduler from 3.6.4 to 3.7.0
- Changed: Future action data stored in the args column on the table _ppfuture_action_args is now camelCase
- Changed: the Database Schema check to verify and display multiple errors at once. The Fix Database should fix them all

- Deprecated: the class `Walker_PostExpirator_Category_Checklist`
- Deprecated: the function `postexpirator_get_post_types`, moving the logic to the model `PostTypesModel`

- Fixed: fatal error when clicking on "Post Types" tab in the settings when using PT-Br language, #567
- Changed: hardcoding the DB engine when creating the table for action arguments, #565 [Thanks to @dave-p]
- Fixed: Simple quotes were not being removed from the future action date offset setting, #566
- Changed: Spanish, French, and Italian translations, #551
- Changed: data sanitization on the plugin, #571
- Fixed: consistency on data saved on post meta from different editors, quick-edit, and bulk-edit. Especially related to the post meta "_expiration-date-options", #573
- Fixed: Strange years value in the date selection, #568
- Fixed: the action "Remove selected term" for authors role, #550
- Fixed: the post type settings page not loading the saved settings after a page refresh triggered by the save button, #576
- Fixed: PHP warning: Creation of dynamic property $hooks in NoticeFacade.php, #580
- Fixed: call to undefined function ...Expirator\Adapters\as_has_scheduled_action, #574
- Fixed: PHP warning: Class ...Expirator\Models\DefaultDataModel not found in ...legacy/deprecated.php, #582
- Changed: the X/Twitter icon on the footer of admin pages, #583
- Fixed: the use of custom taxonomies on the future action panels, #585
- Fixed: call to the method `manageUpgrade` on ...Core\Plugin
- Fixed: action for deleting posts without sending to trash, #593
- Fixed: action that sends a post to trash, to trigger the expected actions, #597
- Fixed: empty cells on Actions table when Pro plugin is uninstalled and Free is activated, #595

- Removed: Internal function `postexpirator_add_footer` was removed, and the footer is now handled in the `ContentController` class
- Removed: Internal function `postexpirator_get_footer_text` was removed

[3.1.3] - 09 Nov, 2023

- Fixed: JS error Cannot read properties of undefined (reading 'length') on the block editor, #561

[3.1.2] - 07 Nov, 2023

- Changed: the library woocommerce/action-scheduler from 3.6.3 to 3.6.4

- Fixed: compatibility with WP 6.4 removing dependency of lodash, #555

[3.1.1] - 11 Oct, 2023

- Added: new bulk action for posts to update future action scheduler based on post's metadata, #538

- Deprecated: class `PublishPress\Future\Core\DI\ContainerNotInitializedException`
- Deprecated: class `PublishPress\Future\Core\DI\ServiceProvider`
- Deprecated: interface `PublishPress\Future\Core\DI\ServiceProviderInterface`

- Fixed: compatibility with 3rd party plugins that import posts, #538
- Fixed: JS error when admin user has no permissions, #533 (Thanks to @raphaelheying)
- Fixed: missed post link on the email notification, or actions log, when the post is deleted, #507
- Fixed: plugin activation hook not running on plugin activation, #539

- Removed: tooltip from the "Expires" column in the posts list, #511

[3.1.0] - 06 Sep, 2023

- Fixed: compatibility with Composer-based installations, using prefixed libraries, #522
- Fixed: notice about using `FILTER_SANITIZE_STRING` on PHP 8, #525

- Removed: the file `define-base-path.php`. The constant `PUBLISHPRESS_FUTURE_BASE_PATH` is deprecated and is now defined in the main plugin file
- Changed: Internal dependencies moved from `vendor` to `lib/vendor`, #522
- Changed: Pimple library with a prefixed version of the library to avoid conflicts with other plugins, #522
- Changed: Psr/Container library with a prefixed version of the library to avoid conflicts with other plugins, #522
- Changed: min PHP version to 7.2.5. If not compatible, the plugin will not execute
- Changed: min WP version to 5.5. If not compatible, the plugin will not execute
- Fixed: internal libraries to the latest versions
- Changed: the priority of the hook `plugins_loaded` on the main plugin file from 10 to 5, #522
- Changed: the `vendor-locator-future` library. Internal vendor is now on a fixed path, `lib/vendor`, #522
- Deprecated: constant `PUBLISHPRESS_FUTURE_VENDOR_PATH` in favor of `PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH`
- Changed: Action Scheduler library to 3.6.2
- Changed: the .pot and .mo files

[3.0.6] - 26 Jul, 2023

- Fixed: JavaScript error on the block editor: Uncaught TypeError: Cannot read properties of undefined (reading 'indexOf'), #517
- Fixed: fatal error on content with shortcode: Call to undefined method ...ExpirablePostModel::getExpirationDateAsUnixTime, #516

[3.0.5] - 25 Jul, 2023

- Added: a setting field to control the style of the Future Action column on posts lists (Advanced tab), #482

- Fixed: the message that prevented to select terms for a future action, #488
- Fixed: the taxonomy field in the Post Types settings page, that was not visible unless you select a taxonomy-related default action, #496
- Fixed: the space after the "reset" button on the calendar field, in the block editor, #465
- Fixed: error displayed when trying to deactivate the plugin with "Preserve data after deactivating the plugin" as "Delete data", #499
- Fixed: DB error when trying to create the action args table, due to DESCRIBE query on a table that does not exist yet, #450
- Fixed: default expiration date time for post type on different timezones
- Fixed: date and time on block editor with different timezones, #498
- Fixed: missed title and post type info in emails or logs when the post is deleted, #507
- Fixed: Undefined variable: gmt_schedule_display_string, in the columns in the Future Action screens, #504
- Changed: ES, FR, and IT translations, #509

- Changed: the label for the terms field in the block editor panel, #483
- Changed: Merge the settings tabs "Diagnostics" and "Tools", #501
- Changed: the .pot file
- Changed: the settings tab "Defaults" to "General"
- Added: some instructions comments to translators
- Developers: The default date interval for global and post type settings now only accepts EN format, $495
- Added: log message when date time offset is invalid when trying to schedule a future action
- Changed: the date format on "Scheduled Date" column in the Future Actions list to use the site timezone and not GMT date. GMT date is now displayed on the tooltip
- Changed: text and buttons labels on Diagnostics and Tools settings tab, #506
- Added: method `getExpirationDateAsUnixTime` to the ExpirablePostModel class
- Changed: method `getTitle` on ExpirablePostModel to return title from args if post is not found anymore
- Changed: method `getPostType` on ExpirablePostModel to return post type from args if post is not found anymore

- Developers: The methods `getDefaultDate` and `getDefaultDateCustom` on SettingsFacade class are deprecated

[3.0.4] - 04 Jul, 2023

- Fixed: "Save changes" notification on block editor when post is not edited, #449
- Fixed: unchecked category on classic editor when editing a post with future action enabled, #481
- Changed: French translation, #473
- Fixed: the plugin initialization to properly load the plugin text domain, and CLI commands
- Fixed: the start of the week on the calendar, honoring the site setting, #484
- Fixed: the taxonomy field for custom post types
- Fixed: consistency in the message in the block editor, compared to classic editor, when no taxonomy is selected
- Changed: the .pot file

- Developers: The name of the block editor component changed from `postexpirator-sidebar` to `publishpress-future-action`, #449
- Changed: the Action Scheduler library from 3.6.0 to 3.6.1

- Removed: internal function `postexpirator_init`

[3.0.3] - 20 Jun, 2023

- Fixed: on the block editor: The "postexpirator-sidebar" plugin has encountered an error and cannot be rendered, #475
- Fixed: message in the future action column: Action scheduled but its definition is not available anymore, #474

- Changed: message when future action data is corrupted for the post

[3.0.2] - 19 Jun, 2023

- Fixed: warning displayed in the classic editor if a taxonomy is not properly selected, #453
- Fixed: typo in a message when a taxonomy is not properly selected
- Fixed: a blank post type label in the Arguments column in the Actions Log list when a post type is not registered anymore
- Fixed: error message in the Future Action column if the action is not found anymore, #454
- Fixed: default date/time offset, #455
- Fixed: label "Action" on a few screens, #458
- Fixed: broken screen due to a long select field in Classic Editor, #458
- Fixed: Future action ordering not working on "Posts" screen, #462
- Changed: .pot file and some translation strings

[3.0.1] - 15 Jun, 2023

- Added: diagnostic check for DB schema in the Settings page

- Changed: privacy for method `PublishPress\Future\Framework\WordPress\Models\PostModel::getPostInstance` from `private` to `protected`

- Fixed: Restore future action data on post meta fields, #452
- Fixed: PHP warning about undefined index 'categoryTaxonomy'
- Fixed: auto-enabled future action on new posts, #447
- Fixed: default future action type on custom post types
- Fixed: First letter of future actions log is not capitalized on some messages in the popup view
- Fixed: log message when actions related to taxonomy terms run

[3.0.0] - 13 Jun, 2023

- Added: Dutch translation files, #429

- Changed: Namespace has been changed from `PublishPressFuture` to `PublishPress\Future`
- Changed: Functions, autoload, class aliases and class loading have been moved into a hook for the action `plugins_loaded` with priority 10
- Changed: Post expiration queue migrated from WP Cron to Action Scheduler library from WooCommerce, #149
- Deprecated: hook "publishpressfuture_expire" in favor of "publishpress_future/run_workflow". New hook has two arguments: postId and action, #149
- Changed: the label "Type" to "Action" in the bulk edit field
- Changed: the capability checked before authorizing API usage. Changed from `edit_posts` to `publishpress_future_expire_post`
- Added: the old post status in the log message when the post expires changing status
- Changed: the text of options in the bulk edit field, for more clearance
- Changed: text of Post Types settings tab
- Changed: Replace "Expiry" with "Actions", #392

- Fixed: PHP warning about undefined index 'terms', #412
- Fixed: error on block editor: can't read "length" of undefined
- Fixed: escaping on a few admin text
- Fixed: text and positions of expiration fields in the bulk edit form
- Fixed: email notifications, #414
- Fixed: PHP Fatal error: Uncaught TypeError: gmdate(): Argument #2 ($timestamp) must be of type ?int, #413
- Fixed: All the expirations scheduled to the future run if we call "wp cron events run --all", #340
- Fixed: Deactivation of the plugin does not remove the cron jobs and settings, #107
- Fixed: Can we make the cron schedule more human-readable, #231
- Fixed: Expiration actions related to taxonomy are not working if default way to expire is not taxonomy related, #409
- Fixed: Database error on a new site install, #424
- Fixed: Bulk Edit Text doesn't match Quick Edit, #422
- Fixed: Expiration Email Notification is not working, #414
- Fixed: Capital case for statuses, #430
- Changed: sure all files have protection against direct access, #436
- Fixed: fatal error sending expiration email, #434, #433

[2.9.2] - 28 Feb, 2023

- Fixed: List of actions in the post type settings is not filtered by post types, #400
- Fixed: Include Statuses as a Default option, #395
- Removed: legacy screenshots from the plugin root dir
- Fixed: i18n issues, #401

[2.9.1] - 23 Feb, 2023

- Fixed: location of wordpress-banners style CSS when started by the Pro plugin, #393

[2.9.0] - 23 Feb, 2023

- Added: new filter for filtering the expiration actions list: `publishpressfuture_expiration_actions`
- Added: new constant `PUBLISHPRESS_FUTURE_BASE_PATH` to define the base path of the plugin
- Added: hooks to extend settings screen
- Added: ads and banners for the Pro plugin

- Developers: the UI for the Post Types settings screen closing the fields if not activated, #335, #378
- Developers: the services container to be used by the Pro plugin
- Changed: the order of some settings field in the Post Types settings screen

- Fixed: hook `transition_post_status` running twice, #337
- Fixed: bug with choosing a taxonomy change as a default, #335
- Fixed: FR and IT translations, #336 (thanks to @wocmultimedia)
- Fixed: HTML escaping for a field on the settings screen
- Fixed: the expiration date column date format
- Fixed: option to clear data on uninstall, removing the debug table
- Fixed: Combining Multiple Cron Events #149

[2.8.3] - 10 Jan, 2023

- Added: new filters for allowing customizing the expiration metabox and the email sent when post is expired, #327 (thanks to Menno)

- Changed: pattern of expiration debug log messages to describe the action in a clearer way and add more details
- Changed: the label and description of the setting field for default date and time expiration offset, #310

- Removed: debug statement, #326
- Fixed: text for default date/time expiration setting description
- Fixed: PHP 8 error and remove extract functions, #328
- Fixed: Simplify setting to set default expiration date/time interval, removing invalid "none" option, #325
- Fixed: Simplify unscheduling removing duplicated code, #329
- Fixed: PHP warning and fatal error when post's expiration categories list is not an array, #330

[2.8.2] - 20 Dec, 2022

- Fixed: taxonomy expiration, #309
- Fixed: TypeError in `ExpirablePostModel.php`: array_unique(): Argument #1 ($array) must be of type array, #318

[2.8.1] - 08 Dec, 2022

- Fixed: PHP warning: attempt to read property "ID" on null in the "the_content" filter, #313
- Fixed: PHP warning: undefined array key "properties" in `class-wp-rest-meta-fields.php`, #311
- Changed: language files to ES, FR, and IT (thanks to @wocmultimedia), #308

[2.8.0] - 08 Nov, 2022

- Added: translations for ES, FR, IT languages, #297

- Changed: the "None" option from default expiration dates. If a site is using it, the default value is now "Custom" and set for "+1 week", #274
- Developers: The code was partially refactored improving the code quality, applying DRY and other good practices
- Deprecated: some internal functions: `postexpirator_activate`, `postexpirator_autoload`, `postexpirator_schedule_event`, `postexpirator_unschedule_event`, `postexpirator_debug`, `_postexpirator_get_cat_names`, `postexpirator_register_expiration_meta`, `postexpirator_expire_post`, `expirationdate_deactivate`
- Deprecated: the constant: `PostExpirator_Facade::PostExpirator_Facade` => `PublishPressFuture\Modules\Expirator\CapabilitiesAbstract::EXPIRE_POST`
- Deprecated: the constant `POSTEXPIRATOR_DEBUG`
- Deprecated: the method `PostExpirator_Facade::set_expire_principles`
- Deprecated: the method `PostExpirator_Facade::current_user_can_expire_posts`
- Deprecated: the method `PostExpirator_Facade::get_default_expiry`
- Deprecated: the method `PostExpirator_Util::get_wp_date`
- Deprecated: the class `PostExpiratorDebug`
- Deprecated: the constants: `POSTEXPIRATOR_VERSION`, `POSTEXPIRATOR_DATEFORMAT`, `POSTEXPIRATOR_TIMEFORMAT`, `POSTEXPIRATOR_FOOTERCONTENTS`, `POSTEXPIRATOR_FOOTERSTYLE`, `POSTEXPIRATOR_FOOTERDISPLAY`, `POSTEXPIRATOR_EMAILNOTIFICATION`, `POSTEXPIRATOR_EMAILNOTIFICATIONADMINS`, `POSTEXPIRATOR_DEBUGDEFAULT`, `POSTEXPIRATOR_EXPIREDEFAULT`, `POSTEXPIRATOR_SLUG`, `POSTEXPIRATOR_BASEDIR`, `POSTEXPIRATOR_BASENAME`, `POSTEXPIRATOR_BASEURL`, `POSTEXPIRATOR_LOADED`, `POSTEXPIRATOR_LEGACYDIR`

- Fixed: the expire date column in WooCommerce products list, #276
- Changed: output escaping on a few views, #235
- Changed: input sanitization, #235
- Added: argument swapping on strings with multiple arguments, #305
- Fixed: Expiration settings not working on Classic Editor, #274
- Fixed: remaining message "Cron event not found!" for expirations that run successfully, #288

[2.7.8] - 17 Oct, 2022

- Changed: "Category" in the expiration options to use a more generic term: "Taxonomy"
- Fixed: typo in the classical metabox (classical editor)

- Fixed: bulk edit when expiration is not enabled for the post type, #281
- Fixed: custom taxonomies support, #50

[2.7.7] - 14 Jul, 2022

- Added: post meta "expiration_log" with expiration log data when post expires

- Fixed: Can't bulk edit posts if hour or minutes are set to 00, #273
- Fixed: When the post expires to draft we don't trigger the status transition actions, #264

[2.7.6] - 13 Jun, 2022

- Fixed: fatal error on cron if debug is not activated, #265

[2.7.5] - 09 Jun, 2022

- Fixed: undefined array key "hook_suffix" warning, #259
- Fixed: Double email sending bug confirmed, #204

[2.7.4] - 07 Jun, 2022

- Added: library to protect breaking site when multiple instances of the plugin are activated
- Changed: order of the debug log, showing now in ASC order
- Changed: bulk edit date fields required, #256

- Fixed: unlocalized string on the taxonomy field (Thanks to Alex Lion), #255
- Fixed: default taxonomy selection for Post Types in the settings, #144
- Fixed: typo in the hook name 'postexpirator_schedule' (Thanks to Nico Mollet), #244
- Fixed: bulk editing for WordPress v6.0, #251
- Fixed: the Gutenberg panel for custom post types created on PODS in WordPress v6.0, #250

[2.7.3] - 27 Jan, 2022

- Fixed: the selection of categories when setting a post to expire, #220

[2.7.2] - 25 Jan, 2022

- Added: the event GUID as tooltip to each post in the Current Cron Schedule list on the Diagnostics page, #214

- Added: more clear debug message if the cron event was not scheduled due to an error
- Developers: the list of cron schedules in the Diagnostics tab adding more post information, #215
- Changed: the admin notice about the plugin renaming

- Fixed: the Expires column in the posts page correctly identifying the post ID on cron event with multiple IDs, #210
- Fixed: wrong function used to escape HTML attributes on a settings page
- Fixed: missed sanitization for some data on admin pages
- Fixed: some false positives given by PHPCS
- Fixed: expiration data processing to avoid processing for deactivated posts
- Fixed: a typo in the diagnostics settings tab
- Fixed: the checkbox state for posts that are not set to expire, #217

[2.7.1] - 12 Jan, 2022

- Added: visual indicator to the cron event status in the settings page, #155
- Added: small help text to the Expires column icon to say if the event is scheduled or not
- Added: additional permission check before loading the settings page
- Added: CLI command to expire a post, #206

- Removed: the plugin description from the settings page, #194
- Deprecated: a not used function called `expirationdate_get_blog_url`
- Fixed: the min required WP to 5.3 due to the requirement of using the function `wp_date`

- Fixed: PHP error while purging the debug log, #135
- Fixed: composer's autoloader path
- Fixed: Code cleanup: removed comments and dead code
- Fixed: the block for direct access to view files
- Added: check for `is_admin` before checking if the user has permission to see the settings page
- Fixed: Avoid running sortable column code if not in the admin
- Fixed: Cross-site scripting (XSS) was possible if a third party allowed HTML or JavaScript into a database setting or language file
- Fixed: the URL for the View Debug Log admin page, #196
- Changed: unopened span tag from a form
- Added: a secondary admin and ajax referer check when saving expiration post data
- Fixed: the option "Preserve data after deactivating the plugin" that was not saving the setting, #198
- Fixed: the post expiration function to make sure a post is not expired if the checkbox is not checked on it, #199
- Fixed: the post expiration meta not being cleaned up after a post expires, #207
- Fixed: the post expiration checkbox status when post type is set to check it by default

[2.7.7] - 14 Jul, 2022

- Added: post meta "expiration_log" with expiration log data when post expires

- Fixed: Can't bulk edit posts if hour or minutes are set to 00, #273
- Fixed: When the post expires to draft we don't trigger the status transition actions, #264

[2.7.6] - 13 Jun, 2022

- Fixed: fatal error on cron if debug is not activated, #265

[2.7.5] - 09 Jun, 2022

- Fixed: undefined array key "hook_suffix" warning, #259
- Fixed: Double email sending bug confirmed, #204

[2.7.4] - 07 Jun, 2022

- Added: library to protect breaking site when multiple instances of the plugin are activated
- Changed: order of the debug log, showing now in ASC order
- Changed: bulk edit date fields required, #256

- Fixed: unlocalized string on the taxonomy field (Thanks to Alex Lion), #255
- Fixed: default taxonomy selection for Post Types in the settings, #144
- Fixed: typo in the hook name 'postexpirator_schedule' (Thanks to Nico Mollet), #244
- Fixed: bulk editing for WordPress v6.0, #251
- Fixed: the Gutenberg panel for custom post types created on PODS in WordPress v6.0, #250

[2.7.3] - 27 Jan, 2022

- Fixed: the selection of categories when setting a post to expire, #220

[2.7.2] - 25 Jan, 2022

- Added: the event GUID as tooltip to each post in the Current Cron Schedule list on the Diagnostics page, #214

- Added: more clear debug message if the cron event was not scheduled due to an error
- Developers: the list of cron schedules in the Diagnostics tab adding more post information, #215
- Changed: the admin notice about the plugin renaming

- Fixed: the Expires column in the posts page correctly identifying the post ID on cron event with multiple IDs, #210
- Fixed: wrong function used to escape HTML attributes on a settings page
- Fixed: missed sanitization for some data on admin pages
- Fixed: some false positives given by PHPCS
- Fixed: expiration data processing to avoid processing for deactivated posts
- Fixed: a typo in the diagnostics settings tab
- Fixed: the checkbox state for posts that are not set to expire, #217

[2.7.1] - 12 Jan, 2022

- Added: visual indicator to the cron event status in the settings page, #155
- Added: small help text to the Expires column icon to say if the event is scheduled or not
- Added: additional permission check before loading the settings page
- Added: CLI command to expire a post, #206

- Removed: the plugin description from the settings page, #194
- Deprecated: a not used function called `expirationdate_get_blog_url`
- Fixed: the min required WP to 5.3 due to the requirement of using the function `wp_date`

- Fixed: PHP error while purging the debug log, #135
- Fixed: composer's autoloader path
- Fixed: Code cleanup: removed comments and dead code
- Fixed: the block for direct access to view files
- Added: check for `is_admin` before checking if the user has permission to see the settings page
- Fixed: Avoid running sortable column code if not in the admin
- Fixed: Cross-site scripting (XSS) was possible if a third party allowed HTML or JavaScript into a database setting or language file
- Fixed: the URL for the View Debug Log admin page, #196
- Changed: unopened span tag from a form
- Added: a secondary admin and ajax referer check when saving expiration post data
- Fixed: the option "Preserve data after deactivating the plugin" that was not saving the setting, #198
- Fixed: the post expiration function to make sure a post is not expired if the checkbox is not checked on it, #199
- Fixed: the post expiration meta not being cleaned up after a post expires, #207
- Fixed: the post expiration checkbox status when post type is set to check it by default

[2.7.0] - 02 Dec, 2021

- Added: new admin menu item: Future, #8

- Changed: the plugin from Post Expirator to PublishPress Future, #14
- Added: the PublishPress footer and branding, #68
- Changed: Separate the settings into different tabs, #97, #98
- Changed: the "General Settings" tab to "Default", #99

- Fixed: the 1hr diff between expiration time when editing and shown in post list, #138
- Fixed: Post Expirator is adding wrong expiry dates to old posts, #160
- Fixed: Post Expirator is setting unwanted expire time for posts, #187

[2.6.3] - 18 Nov, 2021

- Added: setting field for choosing between preserve or delete data when the plugin is deactivated, #137

- Fixed: the timezone applied to time fields, #134
- Added: the timezone string to the time fields, #134
- Fixed: the selected expiring categories on the quick edit panel, #160
- Fixed: E_COMPILER_ERROR when cleaning up the debug table, #183
- Fixed: translation and localization of date and time, #150

[2.6.2] - 04 Nov, 2021

- Fixed: fatal error: Call to a member function add_cap() on null, #167
- Fixed: hierarchical taxonomy selection error for multiple taxonomies, #144
- Fixed: PHP warning: use of undefined constant - assumed 'expireType', #617
- Fixed: translation of strings in the block editor panel, #163
- Fixed: category not being added or removed when the post expires, #170
- Fixed: PHP notice: Undefined variable: merged, #174
- Fixed: category-based expiration for custom post types in classic editor, #179
- Fixed: expiration date being added to old posts when edited, #168

[2.6.1] - 27 Oct, 2021

- Added: post information to the scheduled list for easier debugging, #164
- Added: a review request after a specific period of usage, #103
- Changed: the list of cron tasks, filtering only the tasks related to the plugin, #153

- Fixed: category replace not saving, #159
- Fixed: auto enabled settings, #158
- Fixed: expiration data and cron on Gutenberg style box, #156, #136
- Fixed: the request that loads categories in the Gutenberg style panel, #133
- Fixed: the category replace not working with the new Gutenberg style panel, #127
- Fixed: the default options for the Gutenberg style panel, #145

[2.6.0] - 04 Oct, 2021

- Added: specific capabilities for expiring posts, #141

[2.5.1] - 27 Sep, 2021

- Fixed: Default Expiration Categories cannot be unset, #94
- Fixed: Tidy up design for Classic Editor version, #83
- Fixed: All posts now carry the default expiration, #115
- Fixed: with 2.5.0 and WordPress 5.8.1, #110
- Fixed: show private post types that don't have an admin UI, #116

[2.5.0] - 08 Aug, 2021

- Added: "How to Expire" to Quick Edit, #62
- Added: Support for Gutenberg block editor, #10
- Changed: a default time per post type, #12

- Changed: Settings UI enhancement, #14

- Fixed: Appearance Widgets screen shows PHP Notice, #92
- Changed: the PublishPress Future box from appearing in non-public post types, #78
- Fixed: Hide metabox from Media Library files, #56

[2.4.4] - 22 Jul, 2021

- Fixed: conflict with the plugin WCFM, #60
- Fixed: the Category: Remove option, #61

[2.4.3] - 07 Jul, 2021

- Added: Expose wrappers for legacy functions, #40
- Added: Support for quotes in Default expiry, #43

- Changed: Bulk and Quick Edit boxes default to current date/year, #46

- Fixed: Default expiry duration is broken for future years, #39
- Fixed: Translation bug, #5
- Fixed: Post expiring one year early, #24

[2.4.2]

- Fixed: Bulk edit does not change scheduled event bug, #29
- Fixed: Date not being translated in shortcode, #16
- Fixed: Bulk Edit doesn't work, #4

[2.4.1]

- Fixed: deprecated .live jQuery reference

[2.4.0]

- Fixed: PHP Error with PHP 7

[2.3.1]

- Fixed: PHP Error that snuck in on some installations

[2.3.0]

- Added: Email notification upon post expiration. A global email can be set, blog admins can be selected and/or specific users based on post type can be notified
- Added: Expiration Option Added - Stick/Unstick post is now available
- Added: Expiration Option Added - Trash post is now available
- Added: custom actions that can be hooked into when expiration events are scheduled / unscheduled

- Fixed: HTML Code Issues

[2.2.2]

- Fixed: Quick Edit did not retain the expire type setting, and defaulted back to "Draft". This has been resolved

[2.2.1]

- Fixed: issue with bulk edit not correctly updating the expiration date

[2.2.0]

- Added: Quick Edit - setting expiration date and toggling post expiration status can now be done via quick edit
- Added: Bulk Edit - changing expiration date on posts that already are configured can now be done via bulk edit
- Added: ability to order by Expiration Date in dashboard
- Added: Adjusted formatting on defaults page. Multiple post types are now displayed cleaner

- Fixed: Code Cleanup

[2.1.4]

- Fixed: PHP Strict errors with 5.4+
- Changed: temporary timezone conversion - now using core functions again

[2.1.3]

- Fixed: Default category selection now saves correctly on default settings screen

[2.1.2]

- Added: check to show if WP_CRON is enabled on diagnostics page

- Fixed: Code Cleanup

- Added: form nonce for protection against possible CSRF
- Fixed: XSS issue on settings pages

[2.1.1]

- Added: the option to disable post expirator for certain post types if desired

- Fixed: php warning issue caused when post type defaults are not set

[2.1.0]

- Added: support for hierarchical custom taxonomy
- Developers: custom post type support

- Fixed: debug function to be friendly for scripted calls
- Changed: to only show public custom post types on defaults screen
- Changed: category expiration options for 'pages', which is currently unsupported
- Fixed: Some date calls were getting "double" converted for the timezone pending how other plugins handled date - this issue should now be resolved

[2.0.1]

- Changed: Old option cleanup

- Removed: Removes old scheduled hook - this was not done completely in the 2.0.0 upgrade

[2.0.0]

- Changed: debug calls and logging
- Added: the ability to expire to a "private" post
- Added: the ability to expire by adding or removing categories. The old way of doing things is now known as replacing categories
- Added: Revamped the expiration process - the plugin no longer runs on a minute, hourly, or other schedule. Each expiration event schedules a unique event to run, conserving system resources and making things more efficient
- Developers: The type of expiration event can be selected for each post, directly from the post editing screen
- Added: Ability to set defaults for each post type (including custom posts)
- Changed: `expiration-date` meta value to `_expiration-date`
- Added: Revamped timezone handling to be more correct with WordPress standards and fix conflicts with other plugins
- Added: 'Expires' column on post display table now uses the default date/time formats set for the blog

- Changed: `kses` filter calls when the schedule task runs that was causing code entered as `unfiltered_html` to be removed
- Fixed: some calls of `date` to now use `date_i18n`
- Fixed: Most (if not all) PHP error/warnings should be addressed
- Fixed: `wpdb` calls in the debug class to use `wpdb_prepare` correctly
- Changed: menu capability option from "edit_plugin" to "manage_options"

This is a major update of the core functions of this plugin. All current plugins and settings should be upgraded to the new formats and work as expected. Any posts currently scheduled to be expired in the future will be automatically upgraded to the new format.

[1.6.2]

- Added: the ability to configure the post expirator to be enabled by default for all new posts

- Changed: Some instances of `mktime` to `time`

- Fixed: missing global call for MS installs

[1.6.1]

- Added: option to allow user to select any cron schedule (minute, hourly, twicedaily, daily) - including other defined schedules
- Added: option to set default expiration duration - options are none, custom, or publish time

- Fixed: Tweaked error messages, removed clicks for reset cron event
- Fixed: Switched cron schedule functions to use `current_time('timestamp')`
- Fixed: Cleaned up default values code
- Fixed: Code cleanup - PHP notice

[1.6.0]

- Added: debugging

- Changed: "Upgrade" tab with new "Diagnostics" tab
- Changed: Various code cleanup

- Fixed: invalid HTML
- Fixed: i18n issues with dates
- Fixed: problem when using "Network Activate" - reworked plugin activation process
- Fixed: Reworked expire logic to limit the number of SQL queries needed

[1.5.4]

- Changed: Cleaned up deprecated function calls

[1.5.3]

- Fixed: bug with SQL expiration query (props to Robert & John)

[1.5.2]

- Fixed: bug with shortcode that was displaying the expiration date in the incorrect timezone
- Fixed: typo on settings page with incorrect shortcode name

[1.5.1]

- Fixed: bug that was not allowing custom post types to work

[1.5.0]

- Changed: Expirator Box to Sidebar and cleaned up meta code

- Added: ability to expire post to category

[1.4.3]

- Fixed: issue with 3.0 multisite detection

[1.4.2]

- Added: post expirator POT to /languages folder

- Fixed: issue with plugin admin navigation
- Fixed: timezone issue on plugin options screen

[1.4.1]

- Added: support for custom post types (Thanks Thierry)
- Added: i18n support (Thanks Thierry)

- Fixed: issue where expiration date was not shown in the correct timezone in the footer
- Fixed: issue where on some systems the expiration did not happen when scheduled

[1.4.0]

- Fixed: compatibility issues with WordPress - plugin was originally coded for WPMU - should now work on both
- Fixed: timezone - now uses the same timezone as configured by the blog

- Added: ability to schedule post expiration by minute

After upgrading, you may need to reset the cron schedules. Following on-screen notice if prompted. Previously scheduled posts will not be updated, they will be deleted referencing the old timezone setting. If you wish to update them, you will need to manually update the expiration time.

[1.3.1]

- Fixed: sporadic issue of expired posts not being removed

[1.3.0]

- Fixed: Expiration date is now retained across all post status changes
- Fixed: Modified date/time format options for shortcode postexpirator tag

- Added: the ability to add text automatically to the post footer if expiration date is set

[1.2.1]

- Fixed: issue with display date format not being recognized after upgrade

[1.2.0]

- Changed: Wording from "Expiration Date" to "Post Expirator" and moved the configuration options to the "Settings" tab

- Added: shortcode tag `[postexpirator]` to display the post expiration date within the post
- Added: new setting for the default format

- Fixed: bug where expiration date was removed when a post was auto saved

[1.1.0]

- Fixed: Expired posts retain expiration date

[1.0.0]

- Developers: The initial release
