export default {
  id: 'test_integration',
  label: 'Integration tests',
  phase: 'Testing',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('vendor/bin/codecept run Integration');
  },
};
