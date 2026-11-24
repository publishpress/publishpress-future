<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class PostMetaResolver implements VariableResolverInterface
{
    private $postId;

    private $postMetaCache = [];

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function getType(): string
    {
        return 'post_meta';
    }

    public function getValue(string $name = '')
    {
        if (isset($this->postMetaCache[$name])) {
            return $this->postMetaCache[$name];
        }

        $value = get_post_meta($this->postId, $name, true);
        $this->postMetaCache[$name] = $value;
        return $value;
    }

    public function getValueAsString(string $name = ''): string
    {
        return (string) $this->getValue($name);
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->postId,
        ];
    }

    public function getVariable()
    {
        return $this->postId;
    }

    public function setValue(string $name, $value): void
    {
        $this->postMetaCache[$name] = $value;
    }

    public function __isset($name): bool
    {
        return metadata_exists('post', $this->postId, $name);
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
