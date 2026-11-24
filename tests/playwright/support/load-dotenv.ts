import fs from 'fs';
import path from 'path';

/**
 * Loads project-root `.env` into process.env so generated test values are current.
 */
export function loadDotEnv(): void {
  const file = path.resolve(__dirname, '../../../.env');

  if (!fs.existsSync(file)) {
    return;
  }

  const lines = fs.readFileSync(file, 'utf8').split(/\r?\n/);

  for (const rawLine of lines) {
    const line = rawLine.trim();

    if (line === '' || line.startsWith('#') || !line.includes('=')) {
      continue;
    }

    const [key, ...rest] = line.split('=');
    const trimmedKey = key.trim();

    if (trimmedKey === '') {
      continue;
    }

    if (process.env[trimmedKey] !== undefined && process.env[trimmedKey] !== '') {
      continue;
    }

    let value = rest.join('=').trim();
    value = value.replace(/^['"]|['"]$/g, '');
    value = value.replace(/\\(['"\\$])/g, '$1');

    process.env[trimmedKey] = value;
  }
}
