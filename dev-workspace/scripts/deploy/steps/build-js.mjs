import { commitIfDirty } from '../utils.mjs';

export default {
  id: 'build_js',
  label: 'Build JavaScript assets',
  phase: 'Code Quality',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer build:js');
    await commitIfDirty(ctx, 'Build JavaScript assets');
  },
};
