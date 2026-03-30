<?php

/**
 * Registers injected scripts as first-class Composer Application commands.
 *
 * This allows consumers to call `composer <script>` as a shorthand instead of
 * the longer `composer run <script>` form, which would otherwise fail with
 * "Command not defined" for scripts injected by the plugin via setScripts().
 *
 * @package PublishPress\DevWorkspace
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPL v2 or later
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PublishPress\DevWorkspace;

use Composer\Command\ScriptAliasCommand;

class CommandProvider implements \Composer\Plugin\Capability\CommandProvider
{
    /**
     * Composer lifecycle event names that must not be exposed as runnable commands.
     */
    private const LIFECYCLE_EVENTS = [
        'pre-install-cmd',
        'post-install-cmd',
        'pre-update-cmd',
        'post-update-cmd',
        'pre-autoload-dump',
        'post-autoload-dump',
        'post-root-package-install',
        'post-create-project-cmd',
    ];

    /**
     * Return a ScriptAliasCommand for every non-lifecycle script defined in
     * this package's own composer.json.
     *
     * @return \Composer\Command\ScriptAliasCommand[]
     *
     * @since 1.0.0
     */
    public function getCommands(): array
    {
        $json = json_decode(
            file_get_contents(__DIR__ . '/../composer.json'),
            true
        );
        $scripts      = $json['scripts'] ?? [];
        $descriptions = $json['scripts-descriptions'] ?? [];

        $commands = [];
        foreach (array_keys($scripts) as $scriptName) {
            if (in_array($scriptName, self::LIFECYCLE_EVENTS, true)) {
                continue;
            }
            $commands[] = new ScriptAliasCommand(
                $scriptName,
                $descriptions[$scriptName] ?? null
            );
        }

        return $commands;
    }
}
