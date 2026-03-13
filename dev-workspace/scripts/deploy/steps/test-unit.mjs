export default {
  id: 'test_unit',
  label: 'Unit tests',
  phase: 'Testing',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('vendor/bin/codecept run Unit');
  },
};
