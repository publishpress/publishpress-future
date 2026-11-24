<?php

use PublishPress\Future\Core\Plugin;
use PublishPress\WordPressReviews\ReviewsController;

defined('ABSPATH') || exit;

/**
 * WordPress reviews functions.
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
abstract class PostExpirator_Reviews
{
    /**
     * @var ReviewsController
     */
    private static $reviewController = null;

    public static function init()
    {
        if (is_null(static::$reviewController)) {
            add_filter('post-expirator_wp_reviews_allow_display_notice', [self::class, 'shouldDisplayBanner']);

            self::$reviewController = new ReviewsController(
                'post-expirator',
                'PublishPress Future',
                Plugin::getAssetUrl('images/publishpress-future-256.png')
            );

            self::$reviewController->init();
        }
    }

    public static function shouldDisplayBanner($shouldDisplay)
    {
        global $pagenow;

        if (! is_admin() || ! current_user_can('manage_options')) {
            return false;
        }

        if ($pagenow === 'admin.php' && isset($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ($_GET['page'] === 'publishpress-future') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return true;
            }
        }

        return false;
    }
}
