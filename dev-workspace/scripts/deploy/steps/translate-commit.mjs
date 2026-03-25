export default {
  id: 'translate_commit',
  label: 'Commit translation updates',
  phase: 'Localization',
  type: 'confirm',
  message: (ctx) => `Commit all i18n/translation updates together:\n  git add languages/\n  git commit -m "Update translations for v${ctx.data.version}"\n\nThen continue.`,
};
