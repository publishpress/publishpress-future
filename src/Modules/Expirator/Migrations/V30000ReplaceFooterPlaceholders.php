<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;

\defined('ABSPATH') || exit;

class V30000ReplaceFooterPlaceholders implements MigrationInterface
{
    public const HOOK = ExpiratorHooks::ACTION_MIGRATE_REPLACE_FOOTER_PLACEHOLDERS;

    private $hooksFacade;
    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade
     */
    private $optionsFacade;

    /**
     * @param \PublishPress\Future\Core\HookableInterface $hooksFacade
     * @param OptionsFacade $optionsFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        OptionsFacade $optionsFacade
    ) {
        $this->hooksFacade = $hooksFacade;
        $this->optionsFacade = $optionsFacade;

        $this->hooksFacade->addAction(self::HOOK, [$this, 'migrate']);
        $this->hooksFacade->addAction(
            ExpiratorHooks::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'formatLogActionColumn'],
            10,
            2
        );
    }

    public function migrate()
    {
        // FIXME: Add a flag to know if the migration was already executed. Add a way to force the migration.
        // FIXME: Use the settings facade to get the option, not it directly.
        $currentText = $this->optionsFacade->getOption('expirationdateFooterContents');

        if (empty($currentText)) {
            return;
        }

        $currentText = str_replace('EXPIRATIONFULL', 'ACTIONFULL', $currentText);
        $currentText = str_replace('EXPIRATIONDATE', 'ACTIONDATE', $currentText);
        $currentText = str_replace('EXPIRATIONTIME', 'ACTIONTIME', $currentText);

        $this->optionsFacade->updateOption('expirationdateFooterContents', $currentText);
    }

    /**
     * @param string $text
     * @param array $row
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __('Migrate legacy footer placeholders after v3.0.0', 'post-expirator');
        }
        return $text;
    }
}
