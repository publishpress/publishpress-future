export default {
  id: 'check_quality',
  label: 'Code quality checks (PHP compat, lint, CS, longpath)',
  phase: 'Code Quality',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer check');
  },
};
