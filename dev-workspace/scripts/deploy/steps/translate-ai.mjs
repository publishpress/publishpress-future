export default {
  id: 'translate_ai',
  label: 'Generate AI-assisted translations',
  phase: 'Localization',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer translate');
  },
};
