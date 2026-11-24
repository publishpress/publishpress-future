<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\ModuleInterface;
use PublishPress\Future\Framework\WordPress\Facade\EmailFacade;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooksAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ScheduledActionsModelInterface;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionsModel;

\defined('ABSPATH') || exit;

class PastDueActions implements ModuleInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var ScheduledActionsModelInterface
     */
    private $scheduledActionsModel;

    /**
     * @var CronInterface
     */
    private $cron;

    /**
     * @var OptionsFacade
     */
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailFacade
     */
    private $email;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct(
        HookableInterface $hooks,
        CronInterface $cron,
        OptionsFacade $options,
        LoggerInterface $logger,
        EmailFacade $email,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooks;
        $this->scheduledActionsModel = new ScheduledActionsModel();
        $this->cron = $cron;
        $this->options = $options;
        $this->logger = $logger;
        $this->email = $email;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_INIT,
            [$this, 'checkScheduledActionAndSchedule']
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_CHECK_EXPIRED_ACTIONS,
            [$this, 'checkExpiredActions']
        );

        $this->hooks->addFilter(
            ExpiratorHooksAbstract::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'showTitleInHookColumn'],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_WARN_ABOUT_PAST_DUE_ACTIONS,
            [$this, 'warnAboutPastDueActions']
        );
    }

    public function checkScheduledActionAndSchedule()
    {
        $scheduledActions = $this->cron->getScheduledActions(HooksAbstract::ACTION_CHECK_EXPIRED_ACTIONS);

        if (count($scheduledActions) > 0) {
            return;
        }

        $this->cron->scheduleRecurringActionInSeconds(
            time(),
            DAY_IN_SECONDS,
            HooksAbstract::ACTION_CHECK_EXPIRED_ACTIONS,
            [],
            true
        );
    }

    public function showTitleInHookColumn($title, $hook)
    {
        if ($hook['hook'] === HooksAbstract::ACTION_CHECK_EXPIRED_ACTIONS) {
            return __('Check and warn about past-due actions', 'post-expirator');
        }

        return $title;
    }

    public function checkExpiredActions()
    {
        $pastDueActions = $this->scheduledActionsModel->getPastDuePendingActions();

        if (count($pastDueActions) === 0) {
            return;
        }

        $this->hooks->doAction(HooksAbstract::ACTION_WARN_ABOUT_PAST_DUE_ACTIONS);
    }

    public function warnAboutPastDueActions(): void
    {
        if (! $this->settingsFacade->getPastDueActionsNotificationStatus()) {
            return;
        }

        $this->sendEmail();
    }

    private function sendEmail(): void
    {
        $emailBody = sprintf(
            // translators: %s is the admin URL to the scheduled actions page
            __(
                "You have past-due scheduled actions in PublishPress Future.\n\nPlease check them at %s",
                'post-expirator'
            ),
            admin_url('admin.php?page=publishpress-future-scheduled-actions&status=past-due')
        );

        $emailSubject = __('[PublishPress Future] Past-due Actions Found', 'post-expirator');

        $emailAddresses = $this->settingsFacade->getPastDueActionsNotificationAddressesList();

        if (empty($emailAddresses)) {
            $emailAddresses = [
                $this->options->getOption('admin_email'),
            ];
        }

        if (! empty($emailAddresses)) {
            $this->logger->debug('Sending email warning about past-due actions (' . implode(', ', $emailAddresses) . ')');

            // Send each email.
            foreach ($emailAddresses as $email) {
                if (empty($email)) {
                    continue;
                }

                $emailSent = $this->email->send(
                    $email,
                    $emailSubject,
                    $emailBody,
                    [],
                    []
                );

                $this->logger->debug(
                    sprintf(
                        '%s to %s',
                        $emailSent ? 'Past-due actions email sent' : 'Past-due actions email failed',
                        $email
                    )
                );
            }
        }
    }
}
