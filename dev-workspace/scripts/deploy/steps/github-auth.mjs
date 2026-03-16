import { log } from '@clack/prompts';

export default {
  id: 'github_auth',
  label: 'Verify GitHub CLI authentication',
  phase: 'Pre-flight & Branch Setup',
  type: 'auto',
  critical: true,
  setup: async (ctx) => {
    const result = await ctx.execCapture('gh auth status');
    if (result.code !== 0) {
      log.warn('GitHub CLI is not authenticated. Launching interactive login...');
      await ctx.execInteractive('gh auth login');
    }
    await ctx.execCapture('gh auth setup-git');
  },
  run: async (ctx) => {
    const result = await ctx.execCapture('gh auth status');
    if (result.code !== 0) {
      const err = new Error(
        'GitHub CLI is not authenticated. Please run `gh auth login` and retry.'
      );
      err.stdout = result.stdout;
      err.stderr = result.stderr;
      throw err;
    }
    const whoami = await ctx.execCapture('gh auth status 2>&1');
    const match = whoami.stdout.match(/Logged in to \S+ account (\S+)/);
    if (match) log.success(`GitHub authenticated as: ${match[1]}`);
    await ctx.execCapture('gh auth setup-git');
  },
};
