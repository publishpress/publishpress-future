<?php

/**
 * Routes Playwright pool hosts to their own database.
 *
 * Required from the top of the test site's wp-config.php by the Playwright global setup.
 * http://site03.localhost:8011 uses database pw_site03, http://template.localhost:8011 uses pw_template.
 * Any other host (for example localhost:8011) keeps the default "wordpress" database.
 */

$ppFuturePoolHost = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';

if (preg_match('/^(site\d+|template)\.localhost(?::\d+)?$/', $ppFuturePoolHost, $ppFuturePoolMatch)) {
    // wp-config.php reads the database name through getenv_docker(), so this runs before its defines.
    putenv('WORDPRESS_DB_NAME=pw_' . $ppFuturePoolMatch[1]);

    define('WP_HOME', 'http://' . $ppFuturePoolHost);
    define('WP_SITEURL', 'http://' . $ppFuturePoolHost);

    // Same cookie names on every pool site, so one generated admin session works on all clones.
    define('COOKIEHASH', md5('pp-future-playwright-pool'));
}

unset($ppFuturePoolHost, $ppFuturePoolMatch);
