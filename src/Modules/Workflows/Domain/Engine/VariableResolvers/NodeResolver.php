<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;
use WP_Post;

use function wp_json_encode;

class NodeResolver implements VariableResolverInterface
{
    public string $id;
    public string $name;
    public string $label;
    public string $activation_timestamp;
    public string $slug;
    public int $postId;

    public function __construct($node)
    {
        if (is_object($node)) {
            $node = $node->getVariable();
        } else {
            $node = (array)$node;
        }

        $this->id = '';
        $this->name = (string)$node['name'];
        $this->label = (string)$node['label'];
        $this->activation_timestamp = (string)$node['activation_timestamp'];
        $this->slug = (string)$node['slug'];
        $this->postId = 0;

        if (! isset($node['id']) && isset($node['ID'])) {
            $this->id = (string)$node['ID'];
        }

        if (isset($node['id'])) {
            $this->id = (string)$node['id'];
        }

        if (isset($node['postId'])) {
            $this->postId = (int)$node['postId'];
        }

        if (isset($node['post_id'])) {
            $this->postId = (int)$node['post_id'];
        }
    }

    public function getType(): string
    {
        return 'node';
    }

    public function getValue(string $propertyName = '')
    {
        if (isset($this->$propertyName)) {
            return $this->$propertyName;
        }

        return null;
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string)$this->getValue($property);
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => [
                'id' => $this->id,
                'name' => $this->name,
                'label' => $this->label,
                'activation_timestamp' => $this->activation_timestamp,
                'slug' => $this->slug,
                'postId' => $this->postId,
            ]
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariable()
    {
        return $this->node;
    }

    public function setValue(string $name, $value): void
    {
        if (isset($this->$name)) {
            $this->$name = $value;
        }
    }

    public function __isset($name): bool
    {
        return in_array($name, ['ID', 'id', 'name', 'label', 'activation_timestamp', 'slug', 'postId', 'post_id']);
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        if ($name === 'ID') {
            return $this->id;
        }

        if ($name === 'post_id') {
            return $this->postId;
        }

        return null;
    }

    public function __set($name, $value): void
    {
        if ($name === 'postId' || $name === 'post_id') {
            $this->postId = (int)$value;
            return;
        }

        if ($name === 'id') {
            $this->id = (string)$value;
            return;
        }

        if (isset($this->$name)) {
            $this->$name = $value;
        }
    }

    public function __unset($name): void
    {
        return;
    }

    public function __toString(): string
    {
        return wp_json_encode($this->node);
    }
}
