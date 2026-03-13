export default {
  id: 'branch_setup',
  label: 'Create/checkout release branch',
  phase: 'Pre-flight & Branch Setup',
  type: 'auto',
  run: async (ctx) => {
    // Fetch is best-effort — 15s timeout in case credentials/network hang inside container.
    const fetch = await ctx.execCapture('git fetch origin', { timeout: 15000 });
    if (fetch.code !== 0) {
      ctx.log(`Warning: git fetch failed (${fetch.stderr.trim() || 'no output'}). Continuing with local state.`);
    }

    // Check if the branch already exists locally.
    const ref = await ctx.execCapture(`git show-ref --verify --quiet refs/heads/${ctx.data.branchName}`);
    if (ref.code !== 0) {
      await ctx.exec(`git checkout -b ${ctx.data.branchName}`);
    } else {
      await ctx.exec(`git checkout ${ctx.data.branchName}`);
    }

    // Push is also best-effort with 15s timeout.
    const push = await ctx.execCapture(`git push -u origin ${ctx.data.branchName}`, { timeout: 15000 });
    if (push.code !== 0) {
      ctx.log(`Warning: git push failed. Push the branch manually if needed:\n  git push -u origin ${ctx.data.branchName}`);
    }
  },
};
