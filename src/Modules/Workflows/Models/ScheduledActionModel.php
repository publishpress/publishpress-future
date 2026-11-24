<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use Exception;
use PublishPress\Future\Modules\Workflows\Interfaces\ScheduledActionModelInterface;

\defined('ABSPATH') || exit;

class ScheduledActionModel implements ScheduledActionModelInterface
{
    public const ARGS_MAX_LENGTH = 191;

    /**
     * @var int
     */
    private $actionId;

    /**
     * @var string
     */
    private $hook;

    /**
     * @var string
     */
    private $status;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var array
     */
    private $args;

    /**
     * @throws Exception
     */
    public function loadByActionId(int $id): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $wpdb->prefix . 'actionscheduler_actions',
                $id
            ),
            ARRAY_A
        );

        if (empty($row)) {
            throw new Exception('Scheduled action not found');
        }

        $this->setPropertiesFromRow($row);
    }

    public function loadByActionArg(string $arg, string $value, array $validStatuses = []): void
    {
        global $wpdb;

        $arg = preg_replace('/[^a-zA-Z0-9_\-]/', '', $arg);
        $value = sanitize_text_field($value);

        $where = '';
        if (! empty($validStatuse)) {
            $statuses = implode(',', $validStatuses);
            $statuses = preg_replace('/[^a-zA-Z0-9_\-,]/', '', $statuses);
            $where = ' AND status IN (' . $statuses . ')';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE JSON_UNQUOTE(JSON_EXTRACT(extended_args, '$[0]." . $arg . "')) = %s" . $where, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->prefix . 'actionscheduler_actions',
                $value
            ),
            ARRAY_A
        );


        if (empty($row)) {
            throw new Exception('Scheduled action not found');
        }

        $this->setPropertiesFromRow($row);
    }

    private function setPropertiesFromRow(array $row): void
    {
        $this->actionId = $row['action_id'];
        $this->hook = $row['hook'];
        $this->status = $row['status'];
        $this->priority = $row['priority'];

        $args = $row['extended_args'] ?? [];

        if (empty($args)) {
            $args = $row['args'] ?? [];
        }

        if (is_string($args)) {
            $args = json_decode($args, true);
        }

        $this->args = $args;
    }

    public function getActionId(): int
    {
        return $this->actionId;
    }

    public function getHook(): string
    {
        return $this->hook;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function update(): void
    {
        global $wpdb;

        $encodedArgs = wp_json_encode($this->getArgs());

        $row = [];

        if (strlen($encodedArgs) <= self::ARGS_MAX_LENGTH) {
            $row['args'] = $encodedArgs;
            $row['extended_args'] = null;
        } else {
            $row['args'] = md5($encodedArgs);
            $row['extended_args'] = $encodedArgs;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $wpdb->prefix . 'actionscheduler_actions',
            $row,
            ['action_id' => $this->getActionId()]
        );
    }

    /**
     * Save the action_id on the scheduled action because the action_id
     * is needed to retrieve the args from the wp_ppfuture_workflow_scheduled_steps table and
     * the action_id is not passed to the event listener.
     *
     * @return void
     */
    public function setActionIdOnArgs(): void
    {
        $args = $this->getArgs();

        if (empty($args)) {
            return;
        }

        $args[0]['actionId'] = $this->getActionId();

        $this->setArgs($args);
    }

    public static function argsAreOnNewFormat(array $args): bool
    {
        return isset($args['pluginVersion']) && version_compare($args['pluginVersion'], '4.0.0-alpha.1', '>=');
    }

    public function cancel(): void
    {
        $this->updateStatus('canceled');
        $this->deletePendingAction();
    }

    public function complete(): void
    {
        $this->updateStatus('completed');
        $this->deletePendingAction();
    }

    private function updateStatus(string $status): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->update(
            $wpdb->prefix . 'actionscheduler_actions',
            ['status' => $status],
            ['action_id' => $this->getActionId()]
        );

        $this->status = $status;
    }

    private function deletePendingAction(): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->delete(
            $wpdb->prefix . 'actionscheduler_actions',
            [
                'action_id' => $this->getActionId(),
                'status' => 'pending',
            ]
        );
    }
}
