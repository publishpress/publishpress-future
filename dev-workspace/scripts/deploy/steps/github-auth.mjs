import { log } from '@clack/prompts';

export default {
  id: 'github_auth',
  label: 'Verify GitHub CLI authentication',
  phase: 'Pre-flight & Branch Setup',
  type: 'auto',
  critical: true,
  run: async (ctx) => {
    const result = await ctx.execCapture('gh auth status');
    if (result.code !== 0) {
      const err = new Error(
        'GitHub CLI is not authenticated.\n' +
        'Run the following command in another terminal, then retry:\n\n' +
        '  gh auth login\n'
      );
      err.stdout = result.stdout;
      err.stderr = result.stderr;
      throw err;
    }
    // Extract and display the logged-in account for confirmation
    const whoami = await ctx.execCapture('gh auth status 2>&1');
    const match = whoami.stdout.match(/Logged in to \S+ account (\S+)/);
    if (match) log.success(`GitHub authenticated as: ${match[1]}`);
  },
};
