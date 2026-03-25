<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

use function wp_json_encode;

class NodeResolver implements VariableResolverInterface
{
    private array $data;

    public function __construct($node)
    {
        if (is_object($node)) {
            $node = $node->getVariable();
        } else {
            $node = (array)$node;
        }

        $this->data = [
            'id'                   => '',
            'name'                 => (string)($node['name'] ?? ''),
            'label'                => (string)($node['label'] ?? ''),
            'activation_timestamp' => (string)($node['activation_timestamp'] ?? ''),
            'slug'                 => (string)($node['slug'] ?? ''),
            'postId'               => 0,
        ];

        if (! isset($node['id']) && isset($node['ID'])) {
            $this->data['id'] = (string)$node['ID'];
        }

        if (isset($node['id'])) {
            $this->data['id'] = (string)$node['id'];
        }

        if (isset($node['postId'])) {
            $this->data['postId'] = (int)$node['postId'];
        }

        if (isset($node['post_id'])) {
            $this->data['postId'] = (int)$node['post_id'];
        }
    }

    public function getType(): string
    {
        return 'node';
    }

    public function getValue(string $propertyName = '')
    {
        return $this->__get($propertyName);
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string)$this->getValue($property);
    }

    public function compact(): array
    {
        return [
            'type'  => $this->getType(),
            'value' => [
                'id'                   => $this->data['id'],
                'name'                 => $this->data['name'],
                'label'                => $this->data['label'],
                'activation_timestamp' => $this->data['activation_timestamp'],
                'slug'                 => $this->data['slug'],
                'postId'               => $this->data['postId'],
            ],
        ];
    }

    public function getVariable()
    {
        return $this->data;
    }

    public function setValue(string $name, $value): void
    {
        // Immutable — values cannot be changed after construction.
    }

    public function __isset($name): bool
    {
        if ($name === 'ID' || $name === 'post_id') {
            return true;
        }

        return array_key_exists($name, $this->data);
    }

    public function __get($name)
    {
        if ($name === 'ID') {
            return $this->data['id'] !== '' ? (int)$this->data['id'] : null;
        }

        if ($name === 'post_id') {
            return $this->data['postId'];
        }

        return $this->data[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        // Immutable — assignment is silently ignored.
    }

    public function __unset($name): void
    {
        // Immutable — unset is silently ignored.
    }

    public function __toString(): string
    {
        $output = [];

        if ($this->data['id'] !== '') {
            $output['ID'] = (int)$this->data['id'];
        }

        foreach (['name', 'label', 'activation_timestamp', 'slug'] as $key) {
            if ($this->data[$key] !== '') {
                $output[$key] = $this->data[$key];
            }
        }

        return wp_json_encode($output ?: []);
    }
}
