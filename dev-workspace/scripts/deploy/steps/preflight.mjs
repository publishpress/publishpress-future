export default {
  id: 'preflight',
  label: 'Pre-flight check',
  phase: 'Pre-flight & Branch Setup',
  type: 'auto',
  critical: true,
  run: async (ctx) => {
    const result = await ctx.execCapture('git status --short');
    if (result.stdout.trim()) {
      throw new Error(`Working directory is not clean:\n${result.stdout}`);
    }
    const versionResult = await ctx.execCapture('composer get:version 2>/dev/null || echo ""');
    if (versionResult.stdout.trim()) {
      ctx.log(`Current plugin version: ${versionResult.stdout.trim()}`);
    }
  },
};
