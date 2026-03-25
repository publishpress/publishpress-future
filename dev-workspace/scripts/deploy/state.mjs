import { readFileSync, writeFileSync, existsSync, unlinkSync } from 'fs';
import { resolve } from 'path';

const STATE_FILE = resolve(process.cwd(), '.deploy-state.json');

export function loadState() {
  if (!existsSync(STATE_FILE)) return null;
  try {
    return JSON.parse(readFileSync(STATE_FILE, 'utf8'));
  } catch {
    return null;
  }
}

export function saveState(state) {
  const updated = { ...state, updatedAt: new Date().toISOString() };
  writeFileSync(STATE_FILE, JSON.stringify(updated, null, 2), 'utf8');
}

export function deleteState() {
  if (existsSync(STATE_FILE)) unlinkSync(STATE_FILE);
}

export function createFreshState(version, branchName) {
  return {
    version,
    branchName,
    startedAt: new Date().toISOString(),
    updatedAt: new Date().toISOString(),
    completedSteps: [],
    skippedSteps: [],
    failedSteps: [],
  };
}
