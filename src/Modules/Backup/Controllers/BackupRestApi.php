<?php

namespace PublishPress\Future\Modules\Backup\Controllers;

use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Backup\HooksAbstract as BackupHooksAbstract;
use PublishPress\Future\Modules\Settings\Models\SettingsPostTypesModel;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;
use Throwable;
use WP_REST_Request;
use WP_REST_Response;

class BackupRestApi implements InitializableInterface
{
    private HookableInterface $hooks;

    private string $pluginVersion;

    private SettingsFacade $settingsFacade;

    private LoggerInterface $logger;

    public function __construct(
        HookableInterface $hooks,
        string $pluginVersion,
        SettingsFacade $settingsFacade,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooks;
        $this->pluginVersion = $pluginVersion;
        $this->settingsFacade = $settingsFacade;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_REST_API_INIT,
            [$this, 'registerRestRoutes']
        );
    }

    public function registerRestRoutes()
    {
        $apiNamespace = 'publishpress-future/v1';

        register_rest_route(
            $apiNamespace,
            '/backup/workflows',
            [
                'methods' => 'GET',
                'callback' => [$this, 'getWorkflows'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            $apiNamespace,
            '/backup/export',
            [
                'methods' => 'POST',
                'callback' => [$this, 'exportBackup'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
                'args' => [
                    'exportActionWorkflows' => [
                        'type' => 'boolean',
                    ],
                    'exportActionSettings' => [
                        'type' => 'boolean',
                    ],
                    'workflows' => [
                        'type' => 'array',
                    ],
                    'settings' => [
                        'type' => 'array',
                    ],
                ],
            ]
        );

        register_rest_route(
            $apiNamespace,
            '/backup/import',
            [
                'methods' => 'POST',
                'callback' => [$this, 'importBackup'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );
    }

    public function getWorkflows(WP_REST_Request $request)
    {
        try {
            /** @var WorkflowsModel $workflowsModel */
            $workflowsModel = new WorkflowsModel();

            $workflowsIds = $workflowsModel->getAllWorkflowsIds();

            $workflows = [];

            foreach ($workflowsIds as $workflowId) {
                $workflow = new WorkflowModel();
                $workflow->load($workflowId);

                $workflows[] = [
                    'id' => $workflow->getId(),
                    'title' => $workflow->getTitle(),
                    'status' => $workflow->getStatus(),
                ];
            }

            return new WP_REST_Response(
                [
                    'workflows' => $workflows,
                    'ok' => true,
                ],
                200
            );
        } catch (Throwable $e) {
            $this->logger->error('Error getting workflows: ' . $e->getMessage() . '. File: ' . $e->getFile() . ':' . $e->getLine());

            return new WP_REST_Response(
                [
                    'message' => __('Failed to get workflows. Check the logs for more details.', 'post-expirator'),
                    'ok' => false,
                ],
                400
            );
        }
    }

    public function exportBackup(WP_REST_Request $request)
    {
        try {
            $exportActionWorkflows = $request->get_param('exportActionWorkflows');
            $exportActionSettings = $request->get_param('exportActionSettings');
            $selectedWorkflows = $request->get_param('workflows');
            $selectedSettings = $request->get_param('settings');

            if (! $exportActionWorkflows && ! $exportActionSettings) {
                return new WP_REST_Response(
                    [
                        'message' => 'No export action selected',
                        'ok' => false,
                    ],
                    400
                );
            }

            $exportData = [
                'version' => $this->pluginVersion,
                'date' => date('Y-m-d H:i:s'),
            ];

            if ($exportActionWorkflows) {
                $exportData['workflows'] = $this->exportWorkflows($selectedWorkflows);
            }

            if ($exportActionSettings) {
                $exportData['settings'] = $this->exportSettings($selectedSettings);
            }

            return new WP_REST_Response(
                [
                    'message' => 'Exporting backup',
                    'data' => $exportData,
                    'ok' => true,
                ],
                200
            );
        } catch (Throwable $e) {
            $this->logger->error('Error exporting backup: ' . $e->getMessage() . '. File: ' . $e->getFile() . ':' . $e->getLine());

            return new WP_REST_Response(
                [
                    'message' => __('Failed to export the file. Check the logs for more details.', 'post-expirator'),
                    'ok' => false,
                ],
                400
            );
        }
    }

    private function exportWorkflows(array $selectedWorkflowIds = [])
    {
        /** @var WorkflowsModel $workflowsModel */
        $workflowsModel = new WorkflowsModel();

        $workflowIds = $workflowsModel->getAllWorkflowsIds();

        $workflows = [];

        if (empty($selectedWorkflowIds)) {
            return [];
        }

        foreach ($workflowIds as $workflowId) {
            if (! in_array($workflowId, $selectedWorkflowIds)) {
                continue;
            }

            /** @var WorkflowModel $workflow */
            $workflow = new WorkflowModel();
            $workflow->load($workflowId);

            if ($workflow->getStatus() === 'trash') {
                continue;
            }

            $workflows[] = [
                'id' => $workflow->getId(),
                'title' => $workflow->getTitle(),
                'description' => $workflow->getDescription(),
                'modified_at' => $workflow->getModifiedAt(),
                'status' => $workflow->getStatus(),
                'flow' => $workflow->getFlow(),
            ];
        }

        return $workflows;
    }

    private function exportSettings(array $selectedSettings = [])
    {
        $settings = [];

        if (empty($selectedSettings)) {
            return $settings;
        }

        if (in_array('postTypesDefaults', $selectedSettings)) {
            $settings = array_merge($settings, $this->getPostTypesSettings());
        }

        if (in_array('general', $selectedSettings)) {
            $settings = array_merge($settings, $this->getGeneralSettings());
        }

        if (in_array('notifications', $selectedSettings)) {
            $settings = array_merge($settings, $this->getNotificationsSettings());
        }

        if (in_array('display', $selectedSettings)) {
            $settings = array_merge($settings, $this->getDisplaySettings());
        }

        if (in_array('admin', $selectedSettings)) {
            $settings = array_merge($settings, $this->getAdminSettings());
        }

        if (in_array('advanced', $selectedSettings)) {
            $settings = array_merge($settings, $this->getAdvancedSettings());
        }

        $settings = $this->hooks->applyFilters(BackupHooksAbstract::FILTER_EXPORTED_SETTINGS, $settings, $selectedSettings);

        return $settings;
    }

    private function getPostTypesSettings(): array
    {
        /** @var SettingsPostTypesModel $settingsPostTypesModel */
        $settingsPostTypesModel = new SettingsPostTypesModel();


        $postTypes = $settingsPostTypesModel->getPostTypes();
        $defaults = [];

        foreach ($postTypes as $postType) {
            $defaults[$postType] = $this->settingsFacade->getPostTypeDefaults($postType);
        }

        return [
            'postTypesDefaults' => $defaults,
        ];
    }

    private function getGeneralSettings(): array
    {
        return [
            'general' => $this->settingsFacade->getGeneralSettings(),
        ];
    }

    private function getNotificationsSettings(): array
    {
        return [
            'notifications' => $this->settingsFacade->getNotificationsSettings(),
        ];
    }

    private function getDisplaySettings(): array
    {
        return [
            'display' => $this->settingsFacade->getDisplaySettings(),
        ];
    }

    private function getAdminSettings(): array
    {
        return [
            'admin' => $this->settingsFacade->getAdminSettings(),
        ];
    }

    private function getAdvancedSettings(): array
    {
        return [
            'advanced' => $this->settingsFacade->getAdvancedSettings(),
        ];
    }

    public function importBackup(WP_REST_Request $request)
    {
        try {
            $data = $request->get_param('data');
            $backupData = json_decode($data, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($backupData)) {
                throw new Exception('Invalid data');
            }

            if (!$this->validateBackupStructure($backupData)) {
                throw new Exception('Invalid backup data');
            }

            $workflows = $this->sanitizeWorkflows($backupData['workflows'] ?? []);
            $settings = $this->sanitizeSettings($backupData['settings'] ?? []);

            $this->importWorkflows($workflows);
            $this->importSettings($settings);

            return new WP_REST_Response(
                [
                    'message' => 'Backup imported successfully',
                    'ok' => true,
                ],
                200
            );
        } catch (Throwable $e) {
            $this->logger->error('Error importing backup: ' . $e->getMessage() . '. File: ' . $e->getFile() . ':' . $e->getLine());

            return new WP_REST_Response(
                [
                    'message' => __('Failed to import the file. Check the logs for more details.', 'post-expirator'),
                    'ok' => false,
                ],
                400
            );
        }
    }

    private function validateBackupStructure($data)
    {
        if (isset($data['workflows']) && !is_array($data['workflows'])) {
            return false;
        }

        if (isset($data['settings']) && !is_array($data['settings'])) {
            return false;
        }

        if (isset($data['version']) && !is_string($data['version'])) {
            return false;
        }

        return true;
    }

    private function sanitizeWorkflows($workflows)
    {
        if (!is_array($workflows)) {
            return [];
        }

        $sanitized = [];
        foreach ($workflows as $workflow) {
            if (!is_array($workflow)) {
                continue;
            }

            $sanitized[] = [
                'title' => sanitize_text_field($workflow['title'] ?? ''),
                'description' => sanitize_textarea_field($workflow['description'] ?? ''),
                'status' => in_array($workflow['status'] ?? '', ['publish', 'draft'], true)
                    ? $workflow['status']
                    : 'draft',
                'flow' => $this->sanitizeFlowData($workflow['flow'] ?? []),
            ];
        }

        return $sanitized;
    }

    private function sanitizeFlowData($flow)
    {
        if (!is_array($flow)) {
            return [];
        }

        if (isset($flow['nodes']) && is_array($flow['nodes'])) {
            $sanitizedNodes = [];
            foreach ($flow['nodes'] as $node) {
                if (!is_array($node)) {
                    continue;
                }

                $sanitizedNodes[] = [
                    'type' => sanitize_text_field($node['type'] ?? ''),
                    'data' => $this->sanitizeNodeData($node['data'] ?? []),
                ];
            }
            $flow['nodes'] = $sanitizedNodes;
        }

        return $flow;
    }

    private function sanitizeNodeData($data)
    {
        if (!is_array($data)) {
            return [];
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[sanitize_text_field($key)] = $this->sanitizeNodeData($value);
            } elseif (is_string($value)) {
                $sanitized[sanitize_text_field($key)] = sanitize_text_field($value);
            } else {
                $sanitized[sanitize_text_field($key)] = $value;
            }
        }

        return $sanitized;
    }

    private function sanitizeSettings($settings)
    {
        $allowedSettings = ['postTypesDefaults', 'general', 'notifications', 'display', 'admin', 'advanced'];
        $sanitized = [];

        foreach ($allowedSettings as $key) {
            if (isset($settings[$key]) && is_array($settings[$key])) {
                $sanitized[$key] = $this->sanitizeSettingsArray($settings[$key]);
            }
        }

        return $sanitized;
    }

    private function sanitizeSettingsArray($settingsArray)
    {
        $sanitized = [];
        foreach ($settingsArray as $settingKey => $settingValue) {
            if (is_array($settingValue)) {
                $sanitized[sanitize_text_field($settingKey)] = $this->sanitizeSettingsArray($settingValue);
            } elseif (is_string($settingValue)) {
                $sanitized[sanitize_text_field($settingKey)] = sanitize_text_field($settingValue);
            } else {
                $sanitized[sanitize_text_field($settingKey)] = $settingValue;
            }
        }
        return $sanitized;
    }

    public function importWorkflows($workflows)
    {
        if (!is_array($workflows)) {
            return;
        }

        foreach ($workflows as $workflow) {
            if (!is_array($workflow) || empty($workflow['title'])) {
                continue;
            }

            $workflowModel = new WorkflowModel();
            $workflowModel->createNew();
            $workflowModel->setTitle($workflow['title']);
            $workflowModel->setDescription($workflow['description'] ?? '');
            $workflowModel->setStatus($workflow['status'] ?? 'draft');
            $workflowModel->setFlow($workflow['flow'] ?? []);
            $workflowModel->save();
        }
    }

    public function importSettings($settings)
    {
        if (!is_array($settings)) {
            return;
        }

        if (isset($settings['postTypesDefaults']) && is_array($settings['postTypesDefaults'])) {
            foreach ($settings['postTypesDefaults'] as $postType => $default) {
                if (is_string($postType) && is_array($default)) {
                    $this->settingsFacade->setPostTypeDefaults($postType, $default);
                }
            }
        }

        $settingMethods = [
            'general' => 'setGeneralSettings',
            'notifications' => 'setNotificationsSettings',
            'display' => 'setDisplaySettings',
            'admin' => 'setAdminSettings',
            'advanced' => 'setAdvancedSettings',
        ];

        foreach ($settingMethods as $key => $method) {
            if (isset($settings[$key]) && is_array($settings[$key])) {
                $this->settingsFacade->$method($settings[$key]);
            }
        }

        $this->hooks->doAction(BackupHooksAbstract::ACTION_AFTER_IMPORT_SETTINGS, $settings);
    }
}
