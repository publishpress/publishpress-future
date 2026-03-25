export default {
  id: 'set_version',
  label: 'Update version numbers in plugin files',
  phase: 'Version & Documentation',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec(`composer set:version ${ctx.data.version}`);
  },
};
