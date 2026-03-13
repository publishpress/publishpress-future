export default {
  id: 'translate_download',
  label: 'Download updated translations',
  phase: 'Localization',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer translate:download');
  },
};
