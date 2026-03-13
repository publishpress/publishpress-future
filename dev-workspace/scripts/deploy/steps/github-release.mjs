export default {
  id: 'github_release',
  label: 'Create GitHub release',
  phase: 'Release',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec(
      `gh release create v${ctx.data.version} --target master --generate-notes`
    );
  },
};
