export default {
  id: 'check_quality_php',
  label: 'Code quality: PHP compatibility',
  phase: 'Code Quality',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer check:php');
  },
};
