<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use Exception;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Settings\Models\TaxonomiesModel;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Framework\System\DateTimeHandlerInterface;
use WP_REST_Request;

defined('ABSPATH') or die('Direct access not allowed.');

class RestAPIController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DateTimeHandlerInterface
     */
    private $dateTimeHandler;

    /**
     * @var \Closure
     */
    private $taxonomiesModelFactory;

    /**
     * @param HookableInterface $hooksFacade
     * @param callable $expirablePostModelFactory
     */
    public function __construct(
        HookableInterface $hooksFacade,
        $expirablePostModelFactory,
        \Closure $currentUserModelFactory,
        LoggerInterface $logger,
        DateTimeHandlerInterface $dateTimeHandler,
        \Closure $taxonomiesModelFactory
    ) {
        $this->hooks = $hooksFacade;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->currentUserModel = $currentUserModelFactory();
        $this->logger = $logger;
        $this->dateTimeHandler = $dateTimeHandler;
        $this->taxonomiesModelFactory = $taxonomiesModelFactory;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_REST_API_INIT,
            [$this, 'handleRestAPIInit']
        );
    }

    public function handleRestAPIInit()
    {
        $this->registerRestRoute();
        $this->registerRestField();
    }

    private function registerRestRoute()
    {
        $apiNamespace = 'publishpress-future/v1';

        register_rest_route($apiNamespace, '/post-expiration/(?P<postId>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getFutureActionData'],
            'permission_callback' => function ($request) {
                $postId = $request->get_param('postId');
                return $this->currentUserModel->userCanReadPost($postId);
            },
            'args' => [
                'postId' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
                'extraData' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_array($param);
                    },
                    'sanitize_callback' => function ($param, $request, $key) {
                        return $this->sanitizeExtraData($param);
                    },
                    'required' => false,
                    'type' => 'array',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/post-expiration/(?P<postId>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'saveFutureActionData'],
            'permission_callback' => function ($request) {
                $postId = $request->get_param('postId');
                return $this->currentUserModel->userCanExpirePosts()
                    && $this->currentUserModel->userCanEditPost($postId);
            },
            'args' => [
                'postId' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
                'enabled' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_bool($param);
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                    'required' => false,
                    'type' => 'bool',
                ],
                'date' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                    'sanitize_callback' => 'absint',
                    'required' => true,
                    'type' => 'integer',
                ],
                'action' => [
                    'validate_callback' => function ($param, $request, $key) {
                        // Get available future action actions using the service EXPIRATION_ACTIONS_MODEL.
                        $container = Container::getInstance();
                        $expirationActionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
                        $expirationActions = array_keys($expirationActionsModel->getActions());

                        return in_array($param, $expirationActions) || $param === '';
                    },
                    'sanitize_callback' => 'sanitize_text_field',
                    'required' => true,
                    'type' => 'string',
                ],
                'terms' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_array($param);
                    },
                    'sanitize_callback' => function ($param, $request, $key) {
                        return array_map('absint', $param);
                    },
                    'required' => true,
                    'type' => 'array',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/taxonomies/(?P<postType>[a-z\-_0-9A-Z]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostTypeTaxonomies'],
            'permission_callback' => function () {
                return $this->currentUserModel->userCanExpirePosts();
            },
            'args' => [
                'postType' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return sanitize_key($param);
                    },
                    'sanitize_callback' => 'sanitize_key',
                    'required' => true,
                    'type' => 'string',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/terms/(?P<taxonomy>[a-z\-_0-9A-Z]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getTaxonomyTerms'],
            'permission_callback' => function () {
                return $this->currentUserModel->userCanExpirePosts();
            },
            'args' => [
                'taxonomy' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return sanitize_key($param);
                    },
                    'sanitize_callback' => 'sanitize_key',
                    'required' => true,
                    'type' => 'string',
                ],
            ]
        ]);

        register_rest_route($apiNamespace, '/settings/validate-expire-offset', [
            'methods' => 'POST',
            'callback' => [$this, 'validateTextualDatetime'],
            'permission_callback' => function () {
                return $this->currentUserModel->userCanExpirePosts();
            }
        ]);
    }

    public function validateTextualDatetime(WP_REST_Request $request)
    {
        $isValid = true;
        $message = '';
        $currentTime = '';
        $calculatedTime = '';

        try {
            $offset = $request->get_json_params('offset');
            $offset = sanitize_text_field($offset['offset']);

            $currentTime = $this->dateTimeHandler->formatTimestamp(
                $this->dateTimeHandler->getCurrentTime()
            );
            $calculatedTime = $this->dateTimeHandler->formatTimestamp(
                $this->dateTimeHandler->getCalculatedTimeWithOffset(
                    $this->dateTimeHandler->getCurrentTime(),
                    $offset
                )
            );
        } catch (Exception $e) {
            $isValid = false;
            $message = __('Invalid date time offset.', 'post-expirator');

            $this->logger->error('Invalid date time offset: ' . $offset . ' - ' . $e->getMessage());
        }

        return rest_ensure_response([
            'isValid' => $isValid,
            'message' => $message,
            'calculatedTime' => $calculatedTime,
            'currentTime' => $currentTime,
        ]);
    }

    private function registerRestField()
    {
        $container = Container::getInstance();
        $settingsModelFactory = $container->get(ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY);

        $settingsModel = $settingsModelFactory();
        $settings = $settingsModel->getPostTypesSettings();

        $activePostTypes = array_filter($settings, function ($postTypeSettings) {
            return $postTypeSettings['active'];
        });
        $activePostTypes = array_keys($activePostTypes);

        foreach ($activePostTypes as $postType) {
            register_rest_field(
                $postType,
                'publishpress_future_action',
                [
                    'get_callback' => function ($post) {
                        try {
                            $postModelFactory = $this->expirablePostModelFactory;
                            $postModel = $postModelFactory($post['id']);

                            $date = $postModel->getExpirationDateString(false);
                            $action = $postModel->getExpirationType();
                            $newStatus = $postModel->getExpirationNewStatus();
                            $terms = $postModel->getExpirationCategoryIDs();
                            $taxonomy = $postModel->getExpirationTaxonomy();
                            $extraData = $postModel->getExtraData();
                            if (is_array($extraData) && isset($extraData['extraData'])) {
                                $extraData = $extraData['extraData'];
                            }
                            if (! is_array($extraData) || ! isset($extraData['workflowId'])) {
                                $extraData = [];
                            }

                            if (empty($date)) {
                                $defaultDataModelFactory = Container::getInstance()->get(
                                    ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY
                                );
                                $defaultDataModel = $defaultDataModelFactory->create($post['post_type']);

                                $defaultExpirationDate = $defaultDataModel->getActionDateParts($post['id']);
                                $date = $defaultExpirationDate['iso'];

                                $action = $defaultDataModel->getAction();
                                $newStatus = $defaultDataModel->getNewStatus();
                                $terms = [];
                                $taxonomy = '';
                            }

                            return [
                                'enabled' => $postModel->isExpirationEnabled(),
                                'date' => $date,
                                'action' => $action,
                                'newStatus' => $newStatus,
                                'terms' => $terms,
                                'taxonomy' => $taxonomy,
                                'extraData' => $extraData,
                            ];
                        } catch (Exception $e) {
                            $this->logger->error('Error getting future action data: ' . $e->getMessage());
                            return null;
                        }
                    },
                    'update_callback' => function ($value, $post) {
                        try {
                            if (
                                ! $this->currentUserModel->userCanExpirePosts() ||
                                ! $this->currentUserModel->userCanEditPost($post->ID)
                            ) {
                                return false;
                            }

                            if (isset($value['enabled']) && (bool)$value['enabled']) {
                                $expireType = sanitize_text_field($value['action']);
                                $newStatus = sanitize_key($value['newStatus']);

                                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_DRAFT) {
                                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                                    $newStatus = 'draft';
                                }

                                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE) {
                                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                                    $newStatus = 'private';
                                }

                                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_TRASH) {
                                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                                    $newStatus = 'trash';
                                }

                                $opts = [
                                    'expireType' => $expireType,
                                    'newStatus' => $newStatus,
                                    'category' => array_map('sanitize_text_field', $value['terms']),
                                    'categoryTaxonomy' => sanitize_text_field($value['taxonomy']),
                                ];

                                $modelFactory = $this->taxonomiesModelFactory;
                                $taxonomiesModel = $modelFactory();
                                $opts['category'] = $taxonomiesModel->normalizeTermsCreatingIfNecessary(
                                    $opts['categoryTaxonomy'],
                                    $opts['category']
                                );

                                if (isset($value['extraData']) && is_array($value['extraData']) && !empty($value['extraData'])) {
                                    $opts['extraData'] = $value['extraData'];
                                }

                                $this->hooks->doAction(
                                    HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
                                    $post->ID,
                                    strtotime($value['date']),
                                    $opts
                                );
                                return true;
                            }

                            $this->hooks->doAction(HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION, $post->ID);

                            return true;
                        } catch (Exception $e) {
                            $this->logger->error('Error saving future action data: ' . $e->getMessage());
                            return false;
                        }
                    },
                    'schema' => [
                        'description' => 'Future action',
                        'type' => 'object',
                    ]
                ]
            );
        }
    }

    public function getPostTypeTaxonomies(WP_REST_Request $request)
    {
        $postType = $request->get_param('postType');
        $postType = sanitize_key($postType);

        $taxonomies = get_object_taxonomies($postType, 'objects');
        $taxonomies = array_map(function ($taxonomy) {
            return [
                'name' => $taxonomy->name,
                'label' => $taxonomy->label,
            ];
        }, $taxonomies);

        return rest_ensure_response(['taxonomies' => $taxonomies, 'count' => count($taxonomies)]);
    }

    /**
     * Some plugins like Hide Categories and Products for Woocommerce
     * will hide the terms from the get_terms() query.
     *
     * This filter will remove the exclude param from the query so the
     * terms will be returned correctly in the admin.
     *
     * @param array $params
     *
     * @return array
     */
    public function removeExcludeParamFromTermQuery($params)
    {
        // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
        $params['exclude'] = [];

        return $params;
    }

    private function getUnfilteredTerms($taxonomy)
    {
        $this->hooks->addFilter('get_terms_args', [$this, 'removeExcludeParamFromTermQuery'], 20);
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ]);
        remove_filter('get_terms_args', [$this, 'removeExcludeParamFromTermQuery'], 20);

        return $terms;
    }

    public function getTaxonomyTerms(WP_REST_Request $request)
    {
        $taxonomy = $request->get_param('taxonomy');
        $taxonomy = sanitize_key($taxonomy);

        $terms = $this->getUnfilteredTerms($taxonomy);

        $response = [];
        if (is_wp_error($terms)) {
            $response = ['terms' => [], 'count' => 0, 'taxonomyName' => ''];
        } else {
            $terms = array_map(function ($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                ];
            }, $terms);

            $taxonomyName = get_taxonomy($taxonomy)->labels->name;
            $response = ['terms' => $terms, 'count' => count($terms), 'taxonomyName' => $taxonomyName];
        }

        return rest_ensure_response($response);
    }

    public function getFutureActionData(WP_REST_Request $request)
    {
        $postId = $request->get_param('postId');
        $container = Container::getInstance();
        $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $expirablePostModel = $factory($postId);

        $data = $expirablePostModel->getExpirationDataAsArray();

        $data['extraData']['workflowId'] = $expirablePostModel->getExtraData('workflowId');

        // return the data as a JSON response
        return rest_ensure_response($data);
    }

    public function saveFutureActionData(WP_REST_Request $request)
    {
        $postId = absint($request->get_param('postId'));

        $expirationEnabled = (bool)$request->get_param('enabled');

        if ($expirationEnabled) {
            $opts = [
                'expireType' => sanitize_key($request->get_param('action')),
                'category' => array_map('absint', (array)$request->get_param('terms')),
                'categoryTaxonomy' => sanitize_key($request->get_param('taxonomy'))
            ];

            $extraData = $request->get_param('extraData');
            if (is_array($extraData) && !empty($extraData)) {
                $opts['extraData'] = $this->sanitizeExtraData($extraData);
            }

            $this->hooks->doAction(
                HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
                $postId,
                absint($request->get_param('date')),
                $opts
            );
        } else {
            $this->hooks->doAction(HooksAbstract::ACTION_UNSCHEDULE_POST_EXPIRATION, $postId);
        }

        return rest_ensure_response(true);
    }

    private function sanitizeExtraData($data)
    {
        if (!is_array($data)) {
            if (is_string($data)) {
                return sanitize_text_field($data);
            }
            if (is_numeric($data) || is_bool($data)) {
                return $data;
            }
            return null;
        }

        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = sanitize_key($key);
            $sanitized[$sanitizedKey] = $this->sanitizeExtraData($value);
        }

        return $sanitized;
    }
}
