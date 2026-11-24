<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class UserMetaResolver implements VariableResolverInterface
{
    private $userId;

    private $userMetaCache = [];

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function getType(): string
    {
        return 'user_meta';
    }

    public function getValue(string $name = '')
    {
        if (isset($this->userMetaCache[$name])) {
            return $this->userMetaCache[$name];
        }

        $value = get_user_meta($this->userId, $name, true);
        $this->userMetaCache[$name] = $value;

        return $value;
    }

    public function getValueAsString(string $name = ''): string
    {
        return (string) $this->getValue($name);
    }

    public function compact($name = ''): array
    {
        return $this->getValue($name);
    }

    public function getVariable($name = '')
    {
        return $this->getValue($name);
    }

    public function setValue(string $name, $value): void
    {
        $this->userMetaCache[$name] = $value;
    }

    public function __isset($name): bool
    {
        return metadata_exists('user', $this->userId, $name);
    }

    public function __get($name)
    {
        return $this->getValue($name);
    }

    public function __set($name, $value): void
    {
        return;
    }

    public function __unset($name): void
    {
        return;
    }

    public function __toString(): string
    {
        return $this->getType();
    }
}
