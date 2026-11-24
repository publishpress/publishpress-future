import { spawn } from 'child_process';
import http from 'http';
import type { AddressInfo } from 'net';

import { dockerContainer } from './wp-cli';

export const TEMPLATE_DATABASE = 'pw_template';

export interface PoolSite {
  name: string;
  database: string;
  url: string;
}

const CLONE_CONCURRENCY = 4;
const DROP_GRACE_MS = 10_000;

function sitePort(): string {
  const base = new URL(process.env.WP_TESTS_URL ?? 'http://localhost:8011');

  return base.port === '' ? '' : `:${base.port}`;
}

/**
 * Pool hosts resolve to 127.0.0.1 in Chromium without /etc/hosts entries.
 * tests/playwright/pool/site-router.php maps the host to the pw_<name> database.
 */
export function poolSiteUrl(name: string): string {
  return `http://${name}.localhost${sitePort()}`;
}

function siteFor(index: number): PoolSite {
  const name = `site${String(index).padStart(2, '0')}`;

  return { name, database: `pw_${name}`, url: poolSiteUrl(name) };
}

/**
 * Runs SQL as root inside the db_test container.
 */
export function runSql(sql: string, args: string[] = []): Promise<string> {
  return new Promise((resolve, reject) => {
    const child = spawn(
      'docker',
      ['exec', '-i', dockerContainer('db_test'), 'mariadb', '-uroot', '-proot', ...args],
      { stdio: ['pipe', 'pipe', 'pipe'] },
    );
    let stdout = '';
    let stderr = '';

    child.stdout.on('data', (chunk: Buffer) => {
      stdout += chunk.toString();
    });
    child.stderr.on('data', (chunk: Buffer) => {
      stderr += chunk.toString();
    });
    child.on('error', reject);
    child.on('close', (code) => {
      if (code === 0) {
        resolve(stdout.trim());
        return;
      }

      const message = stderr.replace(/^.*Using a password on the command line.*$/gm, '').trim();
      reject(new Error(`mariadb failed (${code}): ${message}`));
    });
    child.stdin.end(sql);
  });
}

export async function listDatabases(pattern: string): Promise<string[]> {
  const output = await runSql(`SELECT schema_name FROM information_schema.schemata WHERE schema_name LIKE '${pattern}';`, ['-N']);

  return output === '' ? [] : output.split('\n');
}

export async function listTables(database: string): Promise<string[]> {
  const output = await runSql(
    `SELECT table_name FROM information_schema.tables WHERE table_schema = '${database}' AND table_type = 'BASE TABLE';`,
    ['-N'],
  );

  return output === '' ? [] : output.split('\n');
}

export async function dropDatabases(databases: string[]): Promise<void> {
  if (databases.length === 0) {
    return;
  }

  await runSql(databases.map((database) => `DROP DATABASE IF EXISTS \`${database}\`;`).join('\n'));
}

/**
 * Keeps a set of clean pool sites cloned from the template database.
 *
 * Workers lease a site per test over HTTP. Every release clones a new site in the background.
 * The released site is dropped, unless its test failed: then it is kept intact for debugging.
 */
export class SitePool {
  private readonly clean: PoolSite[] = [];

  private readonly waiting: Array<(site: PoolSite) => void> = [];

  private readonly kept: PoolSite[] = [];

  private readonly inFlight = new Set<Promise<void>>();

  private readonly pendingDrops = new Set<NodeJS.Timeout>();

  private readonly cloneQueue: Array<() => void> = [];

  private activeClones = 0;

  private nextIndex = 1;

  private server: http.Server | undefined;

  constructor(private readonly tables: string[]) {}

  get keptSites(): PoolSite[] {
    return [...this.kept];
  }

  async fill(size: number): Promise<void> {
    await Promise.all(Array.from({ length: size }, () => this.track(this.addSite())));
  }

  async listen(): Promise<string> {
    this.server = http.createServer((request, response) => {
      this.handle(request, response).catch((error: unknown) => {
        response.statusCode = 500;
        response.end(error instanceof Error ? error.message : String(error));
      });
    });

    await new Promise<void>((resolve) => {
      this.server?.listen(0, '127.0.0.1', resolve);
    });

    const address = this.server.address() as AddressInfo;

    return `http://127.0.0.1:${address.port}`;
  }

  /**
   * Waits for background clones, then drops every site that is not kept for debugging,
   * including sites whose grace period is still running or whose worker never released them.
   */
  async stop(): Promise<void> {
    await new Promise<void>((resolve) => {
      if (!this.server) {
        resolve();
        return;
      }
      this.server.close(() => resolve());
      this.server.closeAllConnections();
    });

    for (const timer of this.pendingDrops) {
      clearTimeout(timer);
    }
    this.pendingDrops.clear();

    await Promise.allSettled([...this.inFlight]);

    const kept = new Set(this.kept.map((site) => site.database));
    const databases = await listDatabases('pw\\_site%');
    await dropDatabases(databases.filter((database) => !kept.has(database)));
    this.clean.length = 0;
  }

  private async handle(request: http.IncomingMessage, response: http.ServerResponse): Promise<void> {
    if (request.method !== 'POST') {
      response.statusCode = 405;
      response.end();
      return;
    }

    if (request.url === '/lease') {
      const site = await this.lease();

      // The test timed out while waiting, so hand the site to the next lease instead.
      if (request.socket.destroyed) {
        this.offer(site);
        return;
      }

      response.setHeader('Content-Type', 'application/json');
      response.end(JSON.stringify(site));
      return;
    }

    if (request.url === '/release') {
      const body = await readBody(request);
      const { name, keep } = JSON.parse(body) as { name: string; keep: boolean };
      this.release(name, keep);
      response.end();
      return;
    }

    response.statusCode = 404;
    response.end();
  }

  private lease(): Promise<PoolSite> {
    const site = this.clean.shift();

    if (site) {
      return Promise.resolve(site);
    }

    return new Promise((resolve) => {
      this.waiting.push(resolve);
    });
  }

  /**
   * A site is never handed out twice. Requests from the finished test can still be running
   * in PHP, so its database is dropped after a grace period and a new site takes its place.
   */
  private release(name: string, keep: boolean): void {
    const index = Number.parseInt(name.replace(/^site/, ''), 10);
    const site = siteFor(index);

    if (keep) {
      this.kept.push(site);
    } else {
      const timer = setTimeout(() => {
        this.pendingDrops.delete(timer);
        this.track(dropDatabases([site.database]));
      }, DROP_GRACE_MS);
      this.pendingDrops.add(timer);
    }

    this.track(this.addSite());
  }

  private offer(site: PoolSite): void {
    const waiter = this.waiting.shift();

    if (waiter) {
      waiter(site);
      return;
    }

    this.clean.push(site);
  }

  private async addSite(): Promise<void> {
    const site = siteFor(this.nextIndex);
    this.nextIndex += 1;
    await this.acquireCloneSlot();

    try {
      await runSql(this.cloneSql(site.database));
    } finally {
      this.releaseCloneSlot();
    }

    this.offer(site);
  }

  /**
   * Server-side copy: about 0.45 s for the template, faster than a dump and import.
   */
  private cloneSql(database: string): string {
    const statements = [
      `DROP DATABASE IF EXISTS \`${database}\`;`,
      `CREATE DATABASE \`${database}\`;`,
    ];

    for (const table of this.tables) {
      statements.push(`CREATE TABLE \`${database}\`.\`${table}\` LIKE \`${TEMPLATE_DATABASE}\`.\`${table}\`;`);
      statements.push(`INSERT INTO \`${database}\`.\`${table}\` SELECT * FROM \`${TEMPLATE_DATABASE}\`.\`${table}\`;`);
    }

    return statements.join('\n');
  }

  private acquireCloneSlot(): Promise<void> {
    if (this.activeClones < CLONE_CONCURRENCY) {
      this.activeClones += 1;
      return Promise.resolve();
    }

    return new Promise((resolve) => {
      this.cloneQueue.push(() => {
        this.activeClones += 1;
        resolve();
      });
    });
  }

  private releaseCloneSlot(): void {
    this.activeClones -= 1;
    this.cloneQueue.shift()?.();
  }

  private track(work: Promise<void>): Promise<void> {
    const tracked = work.catch((error: unknown) => {
      console.error(`[site-pool] ${error instanceof Error ? error.message : String(error)}`);
    });
    this.inFlight.add(tracked);
    void tracked.finally(() => this.inFlight.delete(tracked));

    return tracked;
  }
}

function readBody(request: http.IncomingMessage): Promise<string> {
  return new Promise((resolve, reject) => {
    let body = '';
    request.on('data', (chunk: Buffer) => {
      body += chunk.toString();
    });
    request.on('end', () => resolve(body));
    request.on('error', reject);
  });
}
