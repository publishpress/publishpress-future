import { execFileSync } from 'child_process';

import { loadDotEnv } from './load-dotenv';

loadDotEnv();

interface PluginRow {
  name: string;
  status: string;
  file: string;
}

export interface CreatedPost {
  id: number;
  slug: string;
}

export interface PostTypeDefaults {
  expireType: string;
  autoEnable: string;
  activeMetaBox: string;
  emailnotification: string;
  'default-expire-type': string;
  'default-custom-date': string;
  taxonomy?: string;
}

/**
 * Option key used by SettingsFacade::getPostTypeDefaults().
 * The facade concatenates ucfirst(post type), for example expirationdateDefaultsPost.
 */
export function postTypeOptionKey(postType: string): string {
  return `expirationdateDefaults${postType.charAt(0).toUpperCase()}${postType.slice(1)}`;
}

export function uniqueKey(prefix: string): string {
  return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

let cachedDockerUser: string | undefined;

/**
 * wp-content is owned by the host user after the Linux volume permission fix.
 * The container default user is www-data, which cannot create upgrade/ or mu-plugins.
 */
function dockerUserArgs(container: string): string[] {
  if (cachedDockerUser === undefined) {
    try {
      const owner = execFileSync(
        'docker',
        ['exec', container, 'stat', '-c', '%u:%g', '/var/www/html/wp-content'],
        { encoding: 'utf8' },
      ).trim();

      cachedDockerUser = /^\d+:\d+$/.test(owner) ? owner : '';
    } catch {
      cachedDockerUser = '';
    }
  }

  return cachedDockerUser === '' ? [] : ['-u', cachedDockerUser];
}

const containerNames = new Map<string, string>();

/**
 * Resolves a dev-workspace test container, for example wp_test_cli or db_test.
 */
export function dockerContainer(service: string): string {
  const cached = containerNames.get(service);

  if (cached !== undefined) {
    return cached;
  }

  const name = lookupContainer(service);
  containerNames.set(service, name);

  return name;
}

function lookupContainer(service: string): string {
  const base = process.env.CONTAINER_NAME || 'publishpress-future';
  const expected = `${base}_env_${service}`;

  try {
    const names = execFileSync(
      'docker',
      ['ps', '--filter', `name=${expected}`, '--format', '{{.Names}}'],
      { encoding: 'utf8' },
    )
      .trim()
      .split('\n')
      .map((name) => name.trim())
      .filter(Boolean);
    const exact = names.find((name) => name === expected);

    if (exact) {
      return exact;
    }

    const prefixed = names.find((name) => name.endsWith(`_${expected}`) || name.endsWith(expected));

    if (prefixed) {
      return prefixed;
    }
  } catch {
    // Fall back to the name from .env when docker ps is unavailable.
  }

  return expected;
}

function phpString(value: string): string {
  return `'${value.replace(/\\/g, '\\\\').replace(/'/g, '\\\'')}'`;
}

let siteUrl: string | undefined;

/**
 * Points WP-CLI at a pool site, for example http://site03.localhost:8011.
 * The site router in wp-config.php picks that site's database from the host.
 */
export function setSiteUrl(url: string | undefined): void {
  siteUrl = url;
}

/**
 * Runs WP-CLI inside the test container, against the current pool site when one is set.
 */
export function wp(args: string[]): string {
  const container = dockerContainer('wp_test_cli');
  const urlArgs = siteUrl === undefined ? [] : [`--url=${siteUrl}`];

  try {
    return execFileSync(
      'docker',
      ['exec', ...dockerUserArgs(container), container, 'wp', '--path=/var/www/html', ...urlArgs, ...args],
      {
        encoding: 'utf8',
        stdio: ['ignore', 'pipe', 'pipe'],
      },
    ).trim();
  } catch (error) {
    const failed = error as { stderr?: Buffer | string; stdout?: Buffer | string; message?: string };
    const stderr = failed.stderr ? failed.stderr.toString() : '';
    const stdout = failed.stdout ? failed.stdout.toString() : '';

    throw new Error(`wp ${args.join(' ')} failed: ${stderr || stdout || failed.message || 'unknown error'}`);
  }
}

export function updateOption(key: string, value: unknown): void {
  const json = JSON.stringify(value);

  try {
    wp(['option', 'update', key, json, '--format=json']);
  } catch (error) {
    const message = error instanceof Error ? error.message : '';
    const encoded = Buffer.from(json).toString('base64');
    const php = `update_option(${phpString(key)}, json_decode(base64_decode(${phpString(encoded)}), true));`;

    try {
      wp(['eval', php]);
    } catch (fallbackError) {
      const fallback = fallbackError instanceof Error ? fallbackError.message : '';
      throw new Error(`${message}\nwp eval fallback failed: ${fallback}`);
    }
  }
}

export function updatePostMeta(postId: number, key: string, value: string): void {
  wp(['post', 'meta', 'update', String(postId), key, value]);
}

export function setPermalinkStructure(structure: string): void {
  wp(['rewrite', 'structure', structure, '--hard']);
}

export function ensureUser(login: string, role: string, password = login): void {
  try {
    wp(['user', 'get', login, '--field=ID']);
    wp(['user', 'update', login, `--user_pass=${password}`, `--role=${role}`]);
  } catch {
    wp(['user', 'create', login, `${login}@example.com`, `--role=${role}`, `--user_pass=${password}`]);
  }
}

export function createPost(title: string, postType = 'post', status = 'publish'): CreatedPost {
  const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  const id = Number(wp([
    'post',
    'create',
    `--post_type=${postType}`,
    `--post_status=${status}`,
    `--post_title=${title}`,
    `--post_name=${slug}`,
    '--post_content=Body',
    '--porcelain',
  ]));

  return { id, slug };
}

export function setPostTypeDefaults(postType: string, overrides: Partial<PostTypeDefaults> = {}): void {
  updateOption(postTypeOptionKey(postType), {
    expireType: 'draft',
    autoEnable: '0',
    activeMetaBox: 'active',
    emailnotification: '',
    'default-expire-type': '',
    'default-custom-date': '',
    ...overrides,
  });
}

function pluginRows(): PluginRow[] {
  const raw = wp(['plugin', 'list', '--fields=name,status,file', '--format=json']);

  return JSON.parse(raw) as PluginRow[];
}

export function ensurePublishPressFuture(): void {
  const plugins = pluginRows();
  const match = plugins.find((plugin) => {
    return plugin.name === 'post-expirator'
      || plugin.name === 'publishpress-future'
      || plugin.file.endsWith('post-expirator.php');
  });

  if (!match) {
    throw new Error('post-expirator is not installed. wp plugin list did not show it.');
  }

  if (match.status !== 'active') {
    wp(['plugin', 'activate', match.file || match.name]);
  }
}

export function ensureClassicEditor(): void {
  const plugins = pluginRows();
  const match = plugins.find((plugin) => plugin.name === 'classic-editor');

  if (!match) {
    wp(['plugin', 'install', 'classic-editor', '--activate']);
  } else if (match.status !== 'active') {
    wp(['plugin', 'activate', match.file || 'classic-editor']);
  }

  updateOption('classic-editor-replace', 'classic');
  updateOption('classic-editor-allow-users', 'disallow');
}

export function ensureBlockEditorPreferred(): void {
  const plugins = pluginRows();
  const match = plugins.find((plugin) => plugin.name === 'classic-editor');

  if (match && match.status === 'active') {
    updateOption('classic-editor-replace', 'block');
  }
}

/**
 * Registers the music post type and tax1–tax4 used by the old pre-tests plugin.
 */
export function ensureAcceptanceFixtures(): void {
  const source = `<?php
add_action('init', static function () {
    register_post_type('music', array(
        'labels' => array('name' => 'Music', 'singular_name' => 'Music'),
        'has_archive' => true,
        'public' => true,
        'rewrite' => array('slug' => 'music'),
        'show_in_rest' => true,
        'supports' => array('editor', 'title'),
    ));
    foreach (array('tax1', 'tax2', 'tax3', 'tax4') as $taxonomy) {
        register_taxonomy($taxonomy, array('post', 'page', 'music'), array(
            'labels' => array('name' => ucfirst($taxonomy), 'singular_name' => $taxonomy),
            'show_ui' => true,
            'show_in_rest' => true,
        ));
    }
}, 1);
`;
  const encoded = Buffer.from(source).toString('base64');
  const php = `
    $dir = WP_CONTENT_DIR . '/mu-plugins';
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = $dir . '/pp-future-playwright-fixtures.php';
    if (@file_put_contents($path, base64_decode(${phpString(encoded)})) === false) {
        echo 'WRITE_FAILED';
    }
  `;

  const result = wp(['eval', php]);

  if (result.includes('WRITE_FAILED')) {
    throw new Error('Could not write wp-content/mu-plugins/pp-future-playwright-fixtures.php');
  }
}

export function pluginSlug(): string {
  return process.env.PLUGIN_SLUG || 'post-expirator';
}

/**
 * Writes a second copy of the plugin, for example post-expirator-2/post-expirator.php,
 * holding only the real plugin header. Instance protection compares plugin headers,
 * so this is enough, and nothing loads twice. Pass a version to make the copy outdated.
 * wp-content/plugins is shared by every pool site, so only serial specs may use it.
 */
export function installPluginCopy(folder: string, version = ''): void {
  const php = `
    $source = WP_PLUGIN_DIR . '/' . ${phpString(pluginSlug())} . '/' . ${phpString(pluginSlug())} . '.php';
    $contents = file_get_contents($source);
    $header = substr($contents, 0, strpos($contents, '*/') + 2);
    if (${phpString(version)} !== '') {
        $header = preg_replace('/^( \\* Version: ).*$/m', '\${1}' . ${phpString(version)}, $header);
    }
    $dir = WP_PLUGIN_DIR . '/' . ${phpString(folder)};
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($dir . '/' . ${phpString(pluginSlug())} . '.php', $header . "\\n\\n// Header-only copy written by the Playwright instance protection spec.\\n");
  `;

  wp(['eval', php]);
}

export function removePluginCopy(folder: string): void {
  const php = `
    $dir = WP_PLUGIN_DIR . '/' . ${phpString(folder)};
    foreach (glob($dir . '/*') ?: array() as $file) {
        unlink($file);
    }
    if (is_dir($dir)) {
        rmdir($dir);
    }
  `;

  wp(['eval', php]);
}

export function scheduleDraftExpiration(postId: number, daysFromNow: number): { unix: number; formatted: string } {
  const php = `
    $date = new DateTime('now', wp_timezone());
    $date->modify('+${daysFromNow} day');
    $date->setTime(12, 0, 0);
    $unix = (int) $date->format('U');
    $container = \\PublishPress\\Future\\Core\\DI\\Container::getInstance();
    $scheduler = $container->get(\\PublishPress\\Future\\Core\\DI\\ServicesAbstract::EXPIRATION_SCHEDULER);
    $scheduler->schedule(${postId}, $unix, array(
        'expireType' => 'draft',
        'category' => null,
        'categoryTaxonomy' => '',
        'enabled' => true,
    ));
    $format = get_option('date_format') . ' ' . get_option('time_format');
    $factory = $container->get(\\PublishPress\\Future\\Core\\DI\\ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
    $postModel = $factory(${postId});
    $stored = (int) $postModel->getExpirationDateAsUnixTime();
    echo json_encode(array(
        'unix' => $stored,
        'formatted' => $container->get(\\PublishPress\\Future\\Core\\DI\\ServicesAbstract::DATETIME)->getWpDate($format, $stored),
    ));
  `;

  return JSON.parse(wp(['eval', php])) as { unix: number; formatted: string };
}

export function formatSiteDate(format: string, unix: number): string {
  return wp(['eval', `echo wp_date(${phpString(format)}, ${unix});`]);
}

export function noonUnixDaysFromNow(days: number): number {
  const php = `
    $date = new DateTime('now', wp_timezone());
    $date->modify('+${days} day');
    $date->setTime(12, 0, 0);
    echo $date->format('U');
  `;

  return Number(wp(['eval', php]));
}

export interface NoonParts {
  month: string;
  day: string;
  year: string;
  formatted: string;
}

export function noonPartsDaysFromNow(days: number): NoonParts {
  const php = `
    $date = new DateTime('now', wp_timezone());
    $date->modify('+${days} day');
    $date->setTime(12, 0, 0);
    $format = get_option('date_format') . ' ' . get_option('time_format');
    echo json_encode(array(
        'month' => $date->format('m'),
        'day' => $date->format('j'),
        'year' => $date->format('Y'),
        'formatted' => wp_date($format, (int) $date->format('U')),
    ));
  `;

  return JSON.parse(wp(['eval', php])) as NoonParts;
}
