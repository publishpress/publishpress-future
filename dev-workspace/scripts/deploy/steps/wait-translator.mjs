export default {
  id: 'wait_translator',
  label: 'Wait for translator review',
  phase: 'Localization',
  type: 'manual',
  instructions: (ctx) => `Open a GitHub issue titled:\n  "Translation Update for Release v${ctx.data.version}"\n\nAssign it to @wocmultimedia (lead translator for ES, FR, IT).\nWait for @wocmultimedia to review and confirm or close the issue.`,
};
