export default {
  id: 'test_unit',
  label: 'Unit tests (run externally)',
  phase: 'Testing',
  type: 'manual',
  instructions:
    'Run Unit tests outside this deploy container (browser-capable environment), then continue.\n\n' +
    'Suggested command:\n' +
    '  composer test Unit',
};
