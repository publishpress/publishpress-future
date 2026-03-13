export default {
  id: 'build_js',
  label: 'Build JavaScript assets',
  phase: 'Build',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer build:js');
  },
};
