// Context class: shared state and helpers passed to every step
export class Context {
  constructor(data = {}) {
    this.data = data; // { version, branchName, ... }
  }

  async exec(cmd, { timeout = 0 } = {}) {
    // Spawn shell command. Capture output silently — do not stream to stdout
    // while a spinner may be active, as interleaved writes break spinner rendering.
    // Captured stderr is surfaced by the engine on failure.
    // timeout: optional ms limit; 0 = no limit.
    const { spawn } = await import('child_process');
    return new Promise((resolve, reject) => {
      const child = spawn(cmd, { shell: true, stdio: ['inherit', 'pipe', 'pipe'] });
      let stdout = '';
      let stderr = '';
      let timedOut = false;

      const timer = timeout > 0
        ? setTimeout(() => {
            timedOut = true;
            child.kill('SIGTERM');
          }, timeout)
        : null;

      child.stdout.on('data', (d) => { stdout += d; });
      child.stderr.on('data', (d) => { stderr += d; });
      child.on('close', (code) => {
        if (timer) clearTimeout(timer);
        if (timedOut) {
          const err = new Error(`Command timed out after ${timeout}ms: ${cmd}`);
          err.stdout = stdout;
          err.stderr = stderr;
          err.exitCode = null;
          reject(err);
        } else if (code === 0) {
          resolve(stdout);
        } else {
          const err = new Error(`Command failed (exit ${code}): ${cmd}`);
          err.stdout = stdout;
          err.stderr = stderr;
          err.exitCode = code;
          reject(err);
        }
      });
    });
  }

  async execInteractive(cmd) {
    // Run command with stdio inherited so the user can interact (e.g. gh auth login).
    // Use when prompts must be visible and the user must respond in the same terminal.
    const { spawn } = await import('child_process');
    return new Promise((resolve, reject) => {
      const child = spawn(cmd, { shell: true, stdio: 'inherit' });
      child.on('close', (code) => {
        if (code === 0) {
          resolve();
        } else {
          const err = new Error(`Command failed (exit ${code}): ${cmd}`);
          err.exitCode = code;
          reject(err);
        }
      });
    });
  }

  async execCapture(cmd, { timeout = 0 } = {}) {
    // Run command and return { stdout, stderr, code } without streaming or throwing.
    // timeout: optional ms limit; 0 = no limit.
    const { exec } = await import('child_process');
    const { promisify } = await import('util');
    const execAsync = promisify(exec);
    try {
      const { stdout, stderr } = await execAsync(cmd, timeout > 0 ? { timeout } : {});
      return { stdout, stderr, code: 0 };
    } catch (err) {
      if (err.killed || err.signal) {
        return { stdout: err.stdout || '', stderr: `Command timed out after ${timeout}ms`, code: null };
      }
      return { stdout: err.stdout || '', stderr: err.stderr || err.message, code: err.code || 1 };
    }
  }

  log(msg) {
    console.log(msg);
  }
}
