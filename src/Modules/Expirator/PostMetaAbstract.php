<?php

namespace PublishPress\Future\Modules\Expirator;

\defined('ABSPATH') || exit;

abstract class PostMetaAbstract
{
    public const EXPIRATION_TIMESTAMP = '_expiration-date';

    public const EXPIRATION_STATUS = '_expiration-date-status';

    public const EXPIRATION_DATE_OPTIONS = '_expiration-date-options';

    public const EXPIRATION_TYPE = '_expiration-date-type';

    public const EXPIRATION_POST_STATUS = '_expiration-date-post-status';

    public const EXPIRATION_TERMS = '_expiration-date-categories';

    public const EXPIRATION_TAXONOMY = '_expiration-date-taxonomy';
}
