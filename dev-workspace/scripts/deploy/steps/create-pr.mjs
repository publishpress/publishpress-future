export default {
  id: 'create_pr',
  label: 'Create release pull request',
  phase: 'Release',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec(
      `gh pr create --title "Release ${ctx.data.version}" --body "Release v${ctx.data.version}" --base main --head ${ctx.data.branchName}`
    );
  },
};
