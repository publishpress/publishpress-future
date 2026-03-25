export default {
  id: 'check_quality_lint',
  label: 'Code quality: lint',
  phase: 'Code Quality',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer check:lint');
  },
};
