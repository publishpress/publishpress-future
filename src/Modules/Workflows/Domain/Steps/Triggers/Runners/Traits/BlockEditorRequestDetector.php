<?php

/**
 * @package PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\Traits
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPL v2 or later
 * @since 4.10.0
 */

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners\Traits;

/**
 * Trait to detect block editor (REST) requests.
 *
 * @since 4.10.0
 */
trait BlockEditorRequestDetector
{
    /**
     * Check if the request is a block editor (REST) request.
     *
     * @return bool True if the request is a REST request, false otherwise.
     * @since 4.10.0
     */
    protected function isBlockEditorRequest(): bool
    {
        return defined('REST_REQUEST') && REST_REQUEST;
    }

    /**
     * Handles block editor request deduplication for a given post.
     *
     * The block editor fires the save hook twice: once for the post and once for
     * the metadata. We skip the first call by setting a transient and only
     * proceed on the second call.
     *
     * @param string $transientKey
     * @return bool
     * @since 4.10.0
     */
    protected function shouldSkipDuplicateBlockEditorRequest(string $transientKey): bool
    {
        if ($this->isBlockEditorRequest()) {
            if (! get_transient($transientKey)) {
                set_transient($transientKey, true, 60);

                return true;
            }
        }

        delete_transient($transientKey);

        return false;
    }
}
