export default {
  id: 'test_integration',
  label: 'Integration tests (run externally)',
  phase: 'Testing',
  type: 'manual',
  instructions:
    'Run Integration tests outside this deploy container (browser-capable environment), then continue.\n\n' +
    'Suggested command:\n' +
    '  composer test Integration',
};
