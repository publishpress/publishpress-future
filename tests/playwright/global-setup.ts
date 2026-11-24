import { execFileSync } from 'child_process';

import type { FullConfig } from '@playwright/test';

import {
  dropDatabases,
  listDatabases,
  listTables,
  poolSiteUrl,
  runSql,
  SitePool,
  TEMPLATE_DATABASE,
} from './support/pool';
import {
  dockerContainer,
  ensureAcceptanceFixtures,
  setSiteUrl,
  updateOption,
  wp,
} from './support/wp-cli';

export interface AdminCookie {
  name: string;
  value: string;
  expires: number;
}

const ROUTER_MARKER = 'pp-future-playwright-pool';

function resolveSpareSites(): number {
  const parsed = Number.parseInt(process.env.PW_SPARE_SITES ?? '', 10);

  return Number.isFinite(parsed) && parsed >= 0 ? parsed : 4;
}

/**
 * Adds one require line at the top of the test site's wp-config.php, once.
 */
function installSiteRouter(): void {
  const slug = process.env.PLUGIN_SLUG || 'post-expirator';
  const router = `__DIR__ . '/wp-content/plugins/${slug}/tests/playwright/pool/site-router.php'`;
  const line = `if (file_exists(${router})) { require_once ${router}; } // ${ROUTER_MARKER}`;
  const php = `
    $file = '/var/www/html/wp-config.php';
    $config = file_get_contents($file);
    if (strpos($config, ${JSON.stringify(ROUTER_MARKER)}) === false) {
        $config = preg_replace('/^<\\?php\\s*\\n/', "<?php\\n" . ${JSON.stringify(line)} . "\\n", $config, 1);
        file_put_contents($file, $config);
    }
  `;
  const container = dockerContainer('wp_test_cli');
  const owner = execFileSync('docker', ['exec', container, 'stat', '-c', '%u:%g', '/var/www/html/wp-config.php'], {
    encoding: 'utf8',
  }).trim();

  execFileSync('docker', ['exec', '-u', owner, container, 'php', '-r', php], { stdio: ['ignore', 'pipe', 'pipe'] });
}

async function grantPoolDatabases(): Promise<void> {
  await runSql("GRANT ALL PRIVILEGES ON `pw\\_%`.* TO 'wordpress'@'%';");
}

function ensureClassicEditorInstalled(): void {
  try {
    wp(['plugin', 'is-installed', 'classic-editor']);
  } catch {
    wp(['plugin', 'install', 'classic-editor']);
  }
}

/**
 * Fresh install with everything the specs used to prepare in beforeAll hooks.
 */
async function buildTemplate(): Promise<AdminCookie[]> {
  const admin = process.env.WP_TESTS_ADMIN_USER || 'admin';
  const password = process.env.WP_TESTS_ADMIN_PASSWORD || 'admin';
  const url = poolSiteUrl('template');

  await runSql(`DROP DATABASE IF EXISTS \`${TEMPLATE_DATABASE}\`; CREATE DATABASE \`${TEMPLATE_DATABASE}\`;`);
  setSiteUrl(url);

  try {
    wp([
      'core',
      'install',
      `--url=${url}`,
      '--title=PublishPress Future Playwright',
      `--admin_user=${admin}`,
      `--admin_password=${password}`,
      '--admin_email=test@example.com',
      '--skip-email',
    ]);
    ensureClassicEditorInstalled();
    wp(['plugin', 'activate', process.env.PLUGIN_SLUG || 'post-expirator', 'classic-editor']);
    // Otherwise the first admin page of every clone redirects to Action Workflows.
    wp(['transient', 'delete', 'publishpress_future_redirect_after_activation']);
    updateOption('classic-editor-replace', 'block');
    updateOption('classic-editor-allow-users', 'disallow');
    updateOption('admin_email_lifespan', 2060442225);
    wp(['rewrite', 'structure', '/%postname%/']);
    wp(['user', 'meta', 'update', admin, 'wp_persisted_preferences', JSON.stringify({
      core: { isComplementaryAreaVisible: true },
      'core/edit-post': { welcomeGuide: false },
    }), '--format=json']);

    return createAdminSession(admin);
  } finally {
    setSiteUrl(undefined);
  }
}

/**
 * Creates a long-lived admin session in the template. Every clone copies its session token,
 * and site-router.php fixes COOKIEHASH, so the same cookies log in on every pool site.
 */
function createAdminSession(admin: string): AdminCookie[] {
  const php = `
    $user = get_user_by('login', ${JSON.stringify(admin)});
    $expiration = time() + 2 * DAY_IN_SECONDS;
    $token = WP_Session_Tokens::get_instance($user->ID)->create($expiration);
    echo json_encode(array(
        array('name' => AUTH_COOKIE, 'value' => wp_generate_auth_cookie($user->ID, $expiration, 'auth', $token), 'expires' => $expiration),
        array('name' => LOGGED_IN_COOKIE, 'value' => wp_generate_auth_cookie($user->ID, $expiration, 'logged_in', $token), 'expires' => $expiration),
    ));
  `;

  return JSON.parse(wp(['eval', php])) as AdminCookie[];
}

export default async function globalSetup(config: FullConfig): Promise<() => Promise<void>> {
  const started = Date.now();

  installSiteRouter();
  await grantPoolDatabases();
  ensureAcceptanceFixtures();
  await dropDatabases(await listDatabases('pw\\_site%'));

  const cookies = await buildTemplate();
  const pool = new SitePool(await listTables(TEMPLATE_DATABASE));
  const size = config.workers + resolveSpareSites();

  await pool.fill(size);
  process.env.PW_POOL_URL = await pool.listen();
  process.env.PW_POOL_ADMIN_COOKIES = JSON.stringify(cookies);
  console.log(`[site-pool] ${size} sites ready for ${config.workers} workers in ${Date.now() - started} ms`);

  return async () => {
    await pool.stop();

    const kept = pool.keptSites;
    if (kept.length > 0) {
      console.log('[site-pool] Sites of failed tests are kept until the next run:');
      for (const site of kept) {
        console.log(`  ${site.url}  (database ${site.database})`);
      }
    }
  };
}
