<?php

/**
 * Composer plugin that injects shared scripts into consuming projects.
 *
 * @package PublishPress\DevWorkspace
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPL v2 or later
 */

declare(strict_types=1);

namespace PublishPress\DevWorkspace;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, Capable
{
    /**
     * Activate the plugin: expose extra keys as env vars and merge shared
     * scripts into the root package before Composer processes commands.
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $root = $composer->getPackage();
        $extra = $root->getExtra();

        $map = [
            'plugin-slug'        => 'PP_PLUGIN_SLUG',
            'plugin-lang-domain' => 'PP_PLUGIN_LANG_DOMAIN',
            'plugin-github-repo' => 'PP_PLUGIN_GITHUB_REPO',
            'plugin-composer-package' => 'PP_PLUGIN_COMPOSER_PACKAGE',
        ];
        foreach ($map as $extraKey => $envKey) {
            if (isset($extra[$extraKey])) {
                putenv($envKey . '=' . $extra[$extraKey]);
                $_ENV[$envKey] = $extra[$extraKey];
            }
        }

        $pluginScripts = $this->loadOwnScripts();
        $root->setScripts(array_merge($pluginScripts, $root->getScripts()));
    }

    /**
     * Declare the Composer capabilities this plugin provides.
     *
     * @return array<string, string>
     *
     * @since 1.0.0
     */
    public function getCapabilities(): array
    {
        return [
            \Composer\Plugin\Capability\CommandProvider::class => CommandProvider::class,
        ];
    }

    /**
     * Load the scripts defined in this package's own composer.json.
     *
     * @return array<string, string|string[]>
     *
     * @since 1.0.0
     */
    private function loadOwnScripts(): array
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/../composer.json'),
            true
        );
        $scripts = $json['scripts'] ?? [];

        // Composer's EventDispatcher requires all listener lists to be arrays,
        // but JSON allows single-command scripts to be plain strings.
        foreach ($scripts as $event => $listeners) {
            if (is_string($listeners)) {
                $scripts[$event] = [$listeners];
            }
        }

        return $scripts;
    }

    /**
     * Deactivate the plugin (no-op).
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * Uninstall the plugin (no-op).
     *
     * @param Composer    $composer
     * @param IOInterface $io
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
