export default {
  id: 'changelog_ready',
  label: 'CHANGELOG.md ready',
  phase: 'Version & Documentation',
  type: 'confirm',
  message: (ctx) => `Update CHANGELOG.md:\n  - Add user-friendly descriptions of all changes\n  - Verify the release date is set to today\n  - Ensure v${ctx.data.version} heading is present\n\nThen continue.`,
};
