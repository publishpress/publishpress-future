import { commitIfDirty } from '../utils.mjs';

export default {
  id: 'translate_ai',
  label: 'Generate AI-assisted translations',
  phase: 'Localization',
  type: 'auto',
  run: async (ctx) => {
    await ctx.exec('composer translate');
    await commitIfDirty(ctx, `Update AI-assisted translations for v${ctx.data.version}`);
  },
};
