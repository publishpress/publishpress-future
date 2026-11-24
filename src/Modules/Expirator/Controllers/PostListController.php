<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PostExpirator_Display;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Models\PostTypesModel;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use Throwable;

\defined('ABSPATH') || exit;

class PostListController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var DBTableSchemaInterface
     */
    private $actionArgsSchema;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HookableInterface $hooksFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        DBTableSchemaInterface $actionArgsSchema,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooksFacade;
        $this->actionArgsSchema = $actionArgsSchema;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addFilter(ExpiratorHooks::FILTER_MANAGE_POSTS_COLUMNS, [$this, 'addColumns'], 10, 2);
        $this->hooks->addFilter(ExpiratorHooks::FILTER_MANAGE_PAGES_COLUMNS, [$this, 'addColumns'], 11, 1);
        $this->hooks->addFilter(ExpiratorHooks::FILTER_POSTS_JOIN, [$this, 'joinExpirationDate'], 10, 2);

        $this->hooks->addAction(ExpiratorHooks::ACTION_MANAGE_PAGES_CUSTOM_COLUMN, [$this, 'managePostsCustomColumn']);
        $this->hooks->addAction(ExpiratorHooks::ACTION_MANAGE_POSTS_CUSTOM_COLUMN, [$this, 'managePostsCustomColumn']);
        $this->hooks->addAction(ExpiratorHooks::ACTION_MANAGE_PAGES_CUSTOM_COLUMN, [$this, 'showEmptyOutputChar'], 20, 2);
        $this->hooks->addAction(ExpiratorHooks::ACTION_MANAGE_POSTS_CUSTOM_COLUMN, [$this, 'showEmptyOutputChar'], 20, 2);
        $this->hooks->addAction(ExpiratorHooks::ACTION_ADMIN_INIT, [$this, 'manageSortableColumns'], 100);
        $this->hooks->addAction(ExpiratorHooks::ACTION_POSTS_ORDER_BY, [$this, 'orderByExpirationDate'], 10, 2);
        $this->hooks->addAction(CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS, [$this, 'enqueueScripts']);
    }

    /**
     * @param array $columns
     * @return array
     */
    public function addColumns($columns, $postType = 'page')
    {
        $container = Container::getInstance();
        $settingsFacade = $container->get(ServicesAbstract::SETTINGS);

        $defaults = $settingsFacade->getPostTypeDefaults($postType);

        // If settings are not configured, show the metabox by default only for posts and pages
        if (
            (! isset($defaults['activeMetaBox']) && in_array($postType, [
                'post',
                'page',
            ], true)) || (is_array(
                $defaults
            ) && in_array((string) $defaults['activeMetaBox'], ['active', '1']))
        ) {
            $columns['expirationdate'] = __('Future Action', 'post-expirator');
        }

        return $columns;
    }

    /**
     * @param string $column
     * @param int $postId
     */
    public function managePostsCustomColumn($column)
    {
        if ($column !== 'expirationdate') {
            return;
        }

        $this->renderColumn();
    }

    /**
     * @param \PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel $expirablePostModel
     * @param string $column
     */
    private function renderColumn()
    {
        global $post;

        $output = '';

        if (! empty($post) && isset($post->ID) && $post->ID > 0) {
            $container = Container::getInstance();
            $settings = $container->get(ServicesAbstract::SETTINGS);

            ob_start();
            PostExpirator_Display::getInstance()->render_template('expire-column', [
                'id' => $post->ID,
                'postType' => $post->post_type,
                'columnStyle' => $settings->getColumnStyle(),
            ]);
            $output = ob_get_clean();
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }

    public function manageSortableColumns()
    {
        try {
            $container = Container::getInstance();
            $postTypesModel = new PostTypesModel($container);
            $postTypes = $postTypesModel->getPostTypes();

            foreach ($postTypes as $postType) {
                $this->hooks->addFilter('manage_edit-' . $postType . '_sortable_columns', [$this, 'sortableColumn']);
            }
        } catch (Throwable $th) {
            $this->logger->error('Error managing sortable columns: ' . $th->getMessage());
        }
    }

    public function sortableColumn($columns)
    {
        $columns['expirationdate'] = 'expirationdate';

        return $columns;
    }

    public function orderByExpirationDate($orderby, $query)
    {
        if (! is_admin()  || ! $query->is_main_query()) {
            return $orderby;
        }

        if ('expirationdate' === $query->get('orderby')) {
            $order = strtoupper($query->get('order'));

            if (
                ! in_array($order, [
                    'ASC',
                    'DESC',
                ], true)
            ) {
                $order = 'ASC';
            }

            $orderby = $this->actionArgsSchema->getTableName() . '.scheduled_date ' . $order;
        }

        return $orderby;
    }

    /**
     * @param string $join
     * @param \WP_Query $query
     * @return string
     */
    public function joinExpirationDate($join, $query)
    {
        global $wpdb;

        if (! is_admin() || ! $query->is_main_query()) {
            return $join;
        }

        $actionArgsSchemaTableName = $this->actionArgsSchema->getTableName();

        if ('expirationdate' === $query->get('orderby')) {
            $join .= " LEFT JOIN {$actionArgsSchemaTableName} ON {$actionArgsSchemaTableName}.post_id = {$wpdb->posts}.ID AND {$actionArgsSchemaTableName}.enabled = '1'";
        }

        return $join;
    }

    public function enqueueScripts($screenId)
    {
        try {
            if ('edit.php' === $screenId) {
                wp_enqueue_style(
                    'postexpirator-edit',
                    Plugin::getAssetUrl('css/edit.css'),
                    false,
                    PUBLISHPRESS_FUTURE_VERSION
                );
            }
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing scripts: ' . $th->getMessage());
        }
    }

    public function showEmptyOutputChar($column, $post)
    {
        if ($column !== 'expirationdate') {
            return;
        }

        $container = Container::getInstance();
        $cachePostsWithFutureActions = $container->get(ServicesAbstract::CACHE_POSTS_WITH_FUTURE_ACTION);
        $post = get_post($post);

        if ($cachePostsWithFutureActions->hasValue((string) $post->ID)) {
            return;
        }

        ?>
        <span aria-hidden="true">—</span>
        <span class="screen-reader-text"><?php echo esc_html__('No future action', 'post-expirator'); ?></span>
        <?php
    }
}
