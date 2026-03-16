import { text, log } from '@clack/prompts';

export default {
  id: 'git_config',
  label: 'Verify git user configuration',
  phase: 'Pre-flight & Branch Setup',
  type: 'auto',
  critical: true,

  // setup() runs before the spinner — safe to prompt interactively.
  setup: async (ctx) => {
    const nameResult = await ctx.execCapture('git config --global user.name');
    const emailResult = await ctx.execCapture('git config --global user.email');

    let name = nameResult.stdout.trim();
    let email = emailResult.stdout.trim();

    // Prefer env vars (from .env) before prompting
    if (!name && process.env.GIT_USER_NAME) ctx.data._gitName = process.env.GIT_USER_NAME.trim();
    if (!email && process.env.GIT_USER_EMAIL) ctx.data._gitEmail = process.env.GIT_USER_EMAIL.trim();
    if (ctx.data._gitName) name = ctx.data._gitName;
    if (ctx.data._gitEmail) email = ctx.data._gitEmail;

    if (name && email) return; // already configured

    log.warn('Git user name/email are not configured in this environment.');

    if (!name) {
      const inputName = await text({
        message: 'Enter your git user.name:',
        validate: (v) => (v.trim() ? undefined : 'Name cannot be empty'),
      });
      if (inputName === Symbol.for('clack:cancel')) process.exit(0);
      ctx.data._gitName = inputName.trim();
    }

    if (!email) {
      const inputEmail = await text({
        message: 'Enter your git user.email:',
        validate: (v) => (v.includes('@') ? undefined : 'Enter a valid email address'),
      });
      if (inputEmail === Symbol.for('clack:cancel')) process.exit(0);
      ctx.data._gitEmail = inputEmail.trim();
    }
  },

  run: async (ctx) => {
    if (ctx.data._gitName) {
      await ctx.exec(`git config --global user.name ${JSON.stringify(ctx.data._gitName)}`);
    }
    if (ctx.data._gitEmail) {
      await ctx.exec(`git config --global user.email ${JSON.stringify(ctx.data._gitEmail)}`);
    }

    // Final verification
    const name = (await ctx.execCapture('git config --global user.name')).stdout.trim();
    const email = (await ctx.execCapture('git config --global user.email')).stdout.trim();
    if (!name) throw new Error('git user.name is still not configured.');
    if (!email) throw new Error('git user.email is still not configured.');

    log.success(`Git identity: ${name} <${email}>`);
  },
};
