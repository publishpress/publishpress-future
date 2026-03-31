import { intro, outro, spinner, confirm, select, text, note, log, cancel } from '@clack/prompts';
import { createWriteStream, mkdirSync, writeFileSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import { Context } from './context.mjs';
import { loadState, saveState, deleteState, createFreshState } from './state.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const REPO_ROOT = resolve(__dirname, '../../..'); // up to repo root from dev-workspace/scripts/deploy/
const CACHE_DIR = resolve(REPO_ROOT, 'dev-workspace-cache');

let logStream = null;

function stripAnsi(str) {
  return str.replace(/\x1B\[[0-9;]*[A-Za-z]/g, '');
}

function setupLogFile() {
  try {
    mkdirSync(CACHE_DIR, { recursive: true });
  } catch {
    return null;
  }
  const logPath = resolve(CACHE_DIR, 'deploy.log');
  try {
    writeFileSync(logPath, '', 'utf8');
    logStream = createWriteStream(logPath, { flags: 'a' });
  } catch {
    return null;
  }

  const origStdoutWrite = process.stdout.write.bind(process.stdout);
  const origStderrWrite = process.stderr.write.bind(process.stderr);

  process.stdout.write = (chunk, encoding, callback) => {
    logStream.write(stripAnsi(typeof chunk === 'string' ? chunk : chunk.toString()));
    return origStdoutWrite(chunk, encoding, callback);
  };

  process.stderr.write = (chunk, encoding, callback) => {
    logStream.write(stripAnsi(typeof chunk === 'string' ? chunk : chunk.toString()));
    return origStderrWrite(chunk, encoding, callback);
  };

  return logPath;
}

function printOutput(err) {
  const stdout = (err.stdout || '').trim();
  const stderr = (err.stderr || '').trim();
  if (stdout) {
    log.info('--- stdout ---');
    process.stdout.write(stdout + '\n');
  }
  if (stderr) {
    log.error('--- stderr ---');
    process.stderr.write(stderr + '\n');
  }
  if (!stdout && !stderr) {
    log.warn('(no output captured)');
  }
}

async function promptOnFailure(err, { critical = false } = {}) {
  log.error(err.message);
  let action;
  do {
    action = await select({
      message: critical
        ? 'This step is required and cannot be skipped. How would you like to proceed?'
        : 'How would you like to proceed?',
      options: [
        { value: 'output', label: 'Show command output' },
        { value: 'retry', label: 'Retry this step' },
        ...(!critical ? [{ value: 'skip', label: 'Skip this step and continue' }] : []),
        { value: 'abort', label: 'Pause and exit (resumable)' },
      ],
    });
    if (action === Symbol.for('clack:cancel')) action = 'abort';
    if (action === 'output') printOutput(err);
  } while (action === 'output');
  return action;
}

function formatRelativeTime(isoDate) {
  const diffMs = Date.now() - new Date(isoDate).getTime();
  const mins = Math.floor(diffMs / 60000);
  if (mins < 1) return 'just now';
  if (mins < 60) return `${mins}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  return `${Math.floor(hrs / 24)}d ago`;
}

function flattenSteps(pipeline) {
  const ids = [];
  for (const entry of pipeline) {
    if (entry.steps) {
      for (const s of entry.steps) ids.push(s.id);
    } else {
      ids.push(entry.id);
    }
  }
  return ids;
}

function resolveField(field, ctx) {
  return typeof field === 'function' ? field(ctx) : (field || '');
}

async function runStep(step, ctx, state) {
  if (state.completedSteps.includes(step.id)) {
    log.success(`${step.label} (already done)`);
    return 'done';
  }

  if (step.skip && step.skip(ctx)) {
    log.warn(`${step.label} (skipped - condition)`);
    state.skippedSteps.push(step.id);
    saveState(state);
    return 'skipped';
  }

  if (step.type === 'confirm') {
    const message = resolveField(step.message, ctx);
    note(message, step.phase);
    const ok = await confirm({ message: 'Mark as done and continue?' });
    if (ok === Symbol.for('clack:cancel')) {
      saveState(state);
      cancel('Deployment paused. Run `composer deploy` to resume.');
      process.exit(0);
    }
    if (!ok) {
      saveState(state);
      cancel('Deployment paused. Run `composer deploy` to resume.');
      process.exit(0);
    }
    state.completedSteps.push(step.id);
    saveState(state);
    return 'done';
  }

  if (step.type === 'manual') {
    const instructions = resolveField(step.instructions, ctx);
    note(instructions, step.phase);
    let action;
    do {
      action = await select({
        message: 'What would you like to do?',
        options: [
          { value: 'done', label: 'Mark as done and continue' },
          { value: 'skip', label: 'Skip this step' },
          { value: 'abort', label: 'Pause and exit (resumable)' },
        ],
      });
      if (action === Symbol.for('clack:cancel')) action = 'abort';
    } while (!action);

    if (action === 'abort') {
      saveState(state);
      cancel('Deployment paused. Run `composer deploy` to resume.');
      process.exit(0);
    }
    if (action === 'skip') {
      state.skippedSteps.push(step.id);
    } else {
      state.completedSteps.push(step.id);
    }
    saveState(state);
    return action === 'skip' ? 'skipped' : 'done';
  }

  // type === 'auto'
  // setup() runs once before the spinner — safe to use interactive prompts here.
  if (typeof step.setup === 'function') {
    await step.setup(ctx);
  }

  while (true) {
    const s = spinner();
    s.start(step.label);
    try {
      await step.run(ctx);
      s.stop(`${step.label}`);
      state.completedSteps.push(step.id);
      saveState(state);
      return 'done';
    } catch (err) {
      s.stop(`${step.label} - FAILED`);
      const action = await promptOnFailure(err, { critical: step.critical });
      if (action === 'abort') {
        saveState(state);
        cancel('Deployment paused. Run `composer deploy` to resume.');
        process.exit(1);
      }
      if (action === 'skip') {
        state.skippedSteps.push(step.id);
        saveState(state);
        return 'skipped';
      }
      // retry — loop continues
    }
  }
}

async function runGroup(entry, ctx, state) {
  const { group, steps } = entry;
  note(`Running group: ${group}`, 'Parallel');

  const results = await Promise.allSettled(
    steps.map(async (step) => {
      if (state.completedSteps.includes(step.id)) {
        log.success(`${step.label} (already done)`);
        return { step, status: 'done' };
      }
      if (typeof step.run !== 'function') {
        const err = new Error(`Step "${step.id}" has type "${step.type}" and cannot run in a parallel group. Move it outside the group.`);
        return { step, status: 'failed', error: err };
      }
      const s = spinner();
      s.start(step.label);
      try {
        await step.run(ctx);
        s.stop(`${step.label}`);
        return { step, status: 'done' };
      } catch (err) {
        s.stop(`${step.label} - FAILED`);
        return { step, status: 'failed', error: err };
      }
    })
  );

  let hasFailure = false;
  for (const result of results) {
    const { step, status, error } = result.value || {};
    if (status === 'done') {
      if (!state.completedSteps.includes(step.id)) {
        state.completedSteps.push(step.id);
      }
    } else if (status === 'failed') {
      hasFailure = true;
      log.error(`${step.label} failed: ${error.message}`);
    }
  }
  saveState(state);

  if (hasFailure) {
    // Collect all failed steps so user can view their output individually
    const failedResults = results
      .map((r) => r.value || {})
      .filter((r) => r.status === 'failed');

    let action;
    do {
      const outputOptions = failedResults.map((r) => ({
        value: `output:${r.step.id}`,
        label: `Show output for: ${r.step.label}`,
      }));
      action = await select({
        message: `Some steps in "${group}" failed. How would you like to proceed?`,
        options: [
          ...outputOptions,
          { value: 'skip', label: 'Skip failed steps and continue' },
          { value: 'abort', label: 'Pause and exit (resumable)' },
        ],
      });
      if (action === Symbol.for('clack:cancel')) action = 'abort';
      if (typeof action === 'string' && action.startsWith('output:')) {
        const stepId = action.slice(7);
        const failed = failedResults.find((r) => r.step.id === stepId);
        if (failed) printOutput(failed.error);
      }
    } while (typeof action === 'string' && action.startsWith('output:'));

    if (action === 'abort') {
      saveState(state);
      cancel('Deployment paused. Run `composer deploy` to resume.');
      process.exit(1);
    }
    for (const result of results) {
      const { step, status } = result.value || {};
      if (status === 'failed') {
        state.skippedSteps.push(step.id);
      }
    }
    saveState(state);
  }
}

function getNextStep(pipeline, state) {
  for (const entry of pipeline) {
    if (entry.parallel && entry.steps) {
      for (const step of entry.steps) {
        if (!state.completedSteps.includes(step.id) && !state.skippedSteps.includes(step.id)) {
          return step.id;
        }
      }
    } else {
      if (!state.completedSteps.includes(entry.id) && !state.skippedSteps.includes(entry.id)) {
        return entry.id;
      }
    }
  }
  return 'done';
}

export async function runDeploy(pipeline) {
  const logPath = setupLogFile();

  let currentState = null;
  const handleSignal = () => {
    if (currentState) {
      saveState(currentState);
      process.stdout.write('\nProgress saved. Run `composer deploy` to resume.\n');
    }
    if (logStream) logStream.end();
    process.exit(0);
  };
  process.on('SIGINT', handleSignal);
  process.on('SIGTERM', handleSignal);

  intro('PublishPress Future — Deploy Wizard');
  if (logPath) log.info('Debug log: dev-workspace-cache/deploy.log');

  const existingState = loadState();
  let state;
  let ctx;

  if (existingState) {
    const updatedAgo = formatRelativeTime(existingState.updatedAt);
    note(
      `Found an in-progress release of v${existingState.version} (updated ${updatedAgo})\nCompleted: ${existingState.completedSteps.length} steps`,
      'Resume?'
    );
    const choice = await select({
      message: 'How would you like to proceed?',
      options: [
        { value: 'resume', label: `Resume from last checkpoint (next: ${getNextStep(pipeline, existingState)})` },
        { value: 'restart', label: 'Restart from beginning' },
        { value: 'abort', label: 'Abort' },
      ],
    });
    if (choice === Symbol.for('clack:cancel') || choice === 'abort') {
      cancel('Aborted.');
      process.exit(0);
    }
    if (choice === 'restart') {
      deleteState();
      state = null;
    } else {
      state = existingState;
    }
  }

  if (!state) {
    const version = await text({
      message: 'Enter the version number to release (e.g. 3.4.5):',
      validate(v) {
        if (!/^\d+\.\d+\.\d+(-[a-zA-Z0-9.]+)?$/.test(v)) {
          return 'Invalid format. Use x.x.x or x.x.x-beta.x';
        }
      },
    });
    if (version === Symbol.for('clack:cancel')) {
      cancel('Aborted.');
      process.exit(0);
    }
    const branchName = `release-${version}`;
    state = createFreshState(version, branchName);
    saveState(state);
    ctx = new Context({ version, branchName });
  } else {
    ctx = new Context({ version: state.version, branchName: state.branchName });
  }

  currentState = state;

  let currentPhase = null;
  for (const entry of pipeline) {
    if (entry.parallel && entry.steps) {
      await runGroup(entry, ctx, state);
    } else {
      if (entry.phase && entry.phase !== currentPhase) {
        currentPhase = entry.phase;
        log.step(`Phase: ${currentPhase}`);
      }
      await runStep(entry, ctx, state);
    }
  }

  deleteState();

  const total = flattenSteps(pipeline).length;
  const done = state.completedSteps.length;
  const skipped = state.skippedSteps.length;

  outro(`Deployment complete! ${done} steps completed, ${skipped} skipped of ${total} total.`);

  if (logStream) logStream.end();
}
