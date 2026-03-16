export default {
  id: 'check_quality_cs',
  label: 'Code quality: coding standards',
  phase: 'Code Quality',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer check:cs');
  },
};
