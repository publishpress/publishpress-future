import { text, log, cancel } from '@clack/prompts';

/**
 * After a step that generates files, check if the working directory is dirty.
 * If it is, show the changed files and prompt for a commit message (with a
 * sensible default), then commit everything.
 *
 * @param {import('./context.mjs').Context} ctx
 * @param {string} defaultMessage - pre-filled commit message shown to the user
 */
export async function commitIfDirty(ctx, defaultMessage) {
  const status = await ctx.execCapture('git status --short');
  if (!status.stdout.trim()) return; // working directory is clean — nothing to do

  log.warn('Working directory has changes after this step:');
  process.stdout.write(status.stdout + '\n');

  const message = await text({
    message: 'Commit message:',
    initialValue: defaultMessage,
    validate: (v) => (v.trim() ? undefined : 'Commit message cannot be empty'),
  });

  if (message === Symbol.for('clack:cancel')) {
    cancel('Deployment paused. Run `composer deploy` to resume.');
    process.exit(0);
  }

  await ctx.exec('git add -A');
  await ctx.exec(`git commit -m ${JSON.stringify(message.trim())}`);
  log.success('Changes committed.');
}
