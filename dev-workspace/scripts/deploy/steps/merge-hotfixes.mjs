export default {
  id: 'merge_hotfixes',
  label: 'Merge hotfixes/features into release branch',
  phase: 'Pre-flight & Branch Setup',
  type: 'confirm',
  message: 'Merge any pending hotfixes or features into the release branch now (via direct merge or PR), then continue.',
};
