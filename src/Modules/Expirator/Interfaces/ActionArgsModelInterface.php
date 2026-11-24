<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Interfaces;

use PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel;

\defined('ABSPATH') || exit;

interface ActionArgsModelInterface
{
    public function load(int $id): bool;

    public function loadByActionId(int $actionId): bool;

    public function loadByPostId(int $postId, bool $filterEnabled = false): bool;

    public function save(): void;

    /**
     * @return int
     */
    public function insert(): int;

    /**
     * @param int|null $postId
     */
    public function disableAllForPost($postId = null): void;

    public function delete(): void;

    public function getId(): int;

    public function getCronActionId(): int;

    public function setCronActionId(int $cronActionId): void;

    public function getPostId(): int;

    public function setPostId(int $postId): void;

    public function getArgs(): array;

    public function getArg(string $key): string;

    public function getAction(): string;

    public function getActionLabel(string $postType = ''): string;

    public function getTaxonomyTerms(): array;

    public function getTaxonomy(): string;

    public function getTaxonomyTermsNames(): array;

    public function setArgs(array $args): void;

    /**
     * @param string $key
     * @param mixed $value
     * @return ActionsArgsModel
     */
    public function setArg(string $key, $value): void;

    public function getCreatedAt(): string;

    public function setCreatedAt(string $createdAt): void;

    /**
     * @deprecated version 3.4.0, use getCreatedAtAsISO8601 or getCreatedAtAsUnixTime
     */
    public function getScheduledDate(): string;

    public function getScheduledDateAsISO8601(): string;

    public function setEnabled(bool $enabled): void;

    public function getEnabled(): bool;

    public function getScheduledDateAsUnixTime(): int;

    /**
     * @deprecated version 3.4.0, use setScheduledDateFromISO8601 or setScheduledDateFromUnixTime
     */
    public function setScheduledDate(string $scheduledDate): void;

    public function setScheduledDateFromISO8601(string $scheduledDate): void;

    public function setScheduledDateFromUnixTime(int $scheduledDate): void;

    public function convertUnixTimeDateToISO8601(int $date): string;

    public function convertISO8601DateToUnixTime(string $date): int;
}
