export default {
  id: 'translate_compile',
  label: 'Compile translation files (MO, JSON, PHP)',
  phase: 'Localization',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer translate:compile');
  },
};
