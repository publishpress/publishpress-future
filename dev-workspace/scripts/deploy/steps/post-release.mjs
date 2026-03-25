export default {
  id: 'post_release',
  label: 'Post-release verification',
  phase: 'Post-Release',
  type: 'manual',
  instructions: (ctx) => [
    `Release v${ctx.data.version} is live!`,
    '',
    'Please verify:',
    '  1. Monitor GitHub Actions:',
    '     https://github.com/publishpress/publishpress-future/actions',
    '  2. Verify WordPress.org plugin page:',
    '     https://wordpress.org/plugins/post-expirator/',
    '  3. Test the update on a staging site',
  ].join('\n'),
};
