export default {
  id: 'team_testing',
  label: 'Team testing approval',
  phase: 'Build & Team Testing',
  type: 'manual',
  instructions: (ctx) => `The build package is at: ./dist/\n\nSend the package to the team for testing.\nWait for their approval before proceeding.`,
};
