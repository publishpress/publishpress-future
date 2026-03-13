export default {
  id: 'commit_release',
  label: 'Commit all release changes',
  phase: 'Version & Documentation',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('git add -A');
    await ctx.exec(`git commit -m "Release v${ctx.data.version}" --allow-empty`);
  },
};
