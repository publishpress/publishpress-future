---
name: publishpress-mcp
description: Implement or extend PublishPress Future MCP actions, native scheduling and workflow operations, typed settings, permissions, Free/Pro discovery and integration tests.
---

# PublishPress Future MCP integration

Hub provides an optional connection through the separately installed WordPress MCP Adapter. Participating plugins register private Abilities API actions independently of Hub; clients use a compatible external MCP connection.

## Connection and permission contract

- Register on `wp_abilities_api_init`, outside admin-only paths, and guard missing API functions. Register the shared `publishpress` category only if `wp_has_ability_category` is false. Keep registration independent of the current user's permissions.
- Use `publishpress-future/{action-name}`, explicit schemas and truthful readonly/destructive/idempotent annotations. Keep `meta.public`, `meta.mcp.public` and `meta.show_in_rest` false. The default public Adapter server must not discover or execute these actions.
- Hub owns connection, authentication and exposure. Future owns native operations. Selected tools and Hub's write switch never replace capabilities, object permissions, active-edition checks or application-password restrictions. Never bypass a denial because a user has the Administrator role; never replace authorization with a nonce or switch the MCP identity.
- Preserve native labels, exact identifiers, field choices/defaults, storage formats, filters and sanitizers. Validate exact JSON types before WordPress scalar coercion. Test empty arrays versus objects, nullable fields and native checkbox representations. Preserve omitted settings, unrelated objects and unknown extension-owned data.
- Validate every target before bulk writes, use current revision hashes, verify native read-back and report partial failures honestly. Preview must not write or dispatch. Irreversible execution/cancellation must not claim rollback or safe automatic retry.

## Future implementation map

- Read the repository's `.cursor/skills/code-style/SKILL.md`. Follow Core/Framework/Modules, PHP 7.4 syntax, `InitializableInterface`, injected `HookableInterface` and the existing `ServicesAbstract`/`services.php` container. Keep WordPress global calls at the `Framework/WordPress/Facade/McpFacade` boundary rather than in business services. Use native models through injected factories.
- `src/Modules/Mcp/Module.php` registers shared providers and invokes `publishpress_future_mcp_register` for the Pro companion. `Registry.php` owns private schema registration and bounded strict-type checks. `FeatureCatalogService` routes native feature scopes without granting access. Do not maintain a plugin inventory in Hub.
- `SchedulesService` uses the native scheduler, `CurrentUserModel`, post-type defaults and expiration action choices. The scheduler expects a site-local wall-clock timestamp, not a UTC Unix timestamp; convert once at the boundary and verify the saved UTC instant. Reject an unrepresentable DST fold before writing. Recheck native action permissions at execution.
- `SettingsService` patches native groups with exact group schemas; `PostTypeSettingsService` handles per-type defaults. Native settings setters may return void, so verify persisted values and compensate failed writes. Refresh native caches after changing options/roles. Never expose arbitrary options, licenses or authentication secrets.
- `WorkflowGraph`, `WorkflowChoices` and `StepValidation` use native step definitions, React-persisted field shapes, handle metadata, JSON-logic sanitization and validation rules. Match native empty/legacy formats without coercing a list to a map. Definitions marked Pro in Free are placeholders, not proof that Pro is active. Native custom-action hook allowlists still apply.
- `WorkflowsService` uses `WorkflowModel` with `getFlow(false)` on reads. Do not call metadata-upgrading getters during discovery. Create/duplicate/import disabled drafts; use object and publish/unpublish checks for active graphs. Use native Trash rather than permanent deletion.
- `PostWorkflowsService` uses native manual-trigger eligibility and hooks. Selection and execution are distinct. Deselecting can cancel pending actions. A dispatch response does not promise downstream completion. Preserve the native per-request loop safeguard.
- `RuntimeService` exposes only native Future post/workflow hooks in Future's scheduler group. It never returns raw job arguments or execution contexts. Running a job checks the current post action identity and native permissions again. Do not clone failed jobs or execute arbitrary hooks.
- Pro extensions live in the Pro source repository. Update its shared-Free dependency through normal packaging; never edit production vendored copies. Actual Pro activation controls availability.

## Coverage and verification

Maintain [feature coverage](../../../docs/mcp-feature-coverage.md), [usage](../../../docs/mcp-integration.md) and [request examples](../../../docs/mcp-prompt-examples.md) for current supported behavior, not development history.

Use native Codeception Integration tests under `tests/codeception/Integration/Modules/Mcp`. `tests/Mcp/smoke.php` exercises full native bootstrap, schemas, every settings field, permissions, snapshots/recovery and released Adapter execution. It requires `FUTURE_MCP_MODE=free|pro` and a disposable `mcp_smoke_` WordPress database; Pro mode must load the actual Pro distribution. Pass the Adapter directory as its second argument.

`tests/Mcp/execution.php` verifies real effects in separate requests: `setup`, `manual`, Pro-only `row`, Pro-only `notice`, then `scheduler` (cleanup). Do not combine native manual-trigger executions into one request or weaken its loop protection to make a test pass. Keep fixture bootstrap outside production source.

Verify missing-API loading, category coexistence, denied capabilities on Administrators, object ownership, malformed inputs, stale revisions, Free/Pro boundaries and private default-server rejection. Use the released Adapter, not only direct method calls. Keep test credentials synthetic. Shared transport/session tests remain in Hub.
