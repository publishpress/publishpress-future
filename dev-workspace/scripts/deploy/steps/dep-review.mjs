export default {
  id: 'dep_review',
  label: 'Review dependency changes',
  phase: 'Dependencies',
  type: 'confirm',
  message: `Review the dry-run output above. If updates are needed:\n  1. Run: composer update the/lib:version-constraint\n  2. Lock versions if needed\n  3. Document changes in CHANGELOG.md\n\nOnce done (or if no updates needed), continue.`,
};
