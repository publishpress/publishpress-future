<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Interfaces;

\defined('ABSPATH') || exit;

interface SchedulerInterface
{
    /**
     * @param int $postId
     * @param int $timestamp
     * @param array $opts
     * @return void
     */
    public function schedule($postId, $timestamp, $opts);

    /**
     * @param int $postId
     * @return void
     */
    public function unschedule($postId);

    /**
     * @param int $postId
     * @return bool
     */
    public function postIsScheduled($postId);
}
