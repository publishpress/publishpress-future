<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

use function wp_json_encode;

\defined('ABSPATH') || exit;

class ArrayResolver implements VariableResolverInterface
{
    /**
     * @var array
     */
    private $variable;

    public function __construct(array $variable)
    {
        $this->variable = $variable;
    }

    public function getType(): string
    {
        return 'array';
    }

    public function getValue(string $propertyName = '')
    {
        if (empty($propertyName)) {
            return $this->variable;
        }

        if (isset($this->variable[$propertyName])) {
            return $this->variable[$propertyName];
        }

        return null;
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string) $this->getValue($property);
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->variable,
        ];
    }

    /**
     * @return mixed
     */
    public function getVariable()
    {
        return $this->variable;
    }

    public function setValue(string $name, $value): void
    {
        if (isset($this->variable[$name])) {
            $this->variable[$name] = $value;
        }
    }

    public function __isset($name): bool
    {
        return isset($this->variable[$name]);
    }

    public function __get($name)
    {
        if (isset($this->variable[$name])) {
            return $this->variable[$name];
        }

        return null;
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
        return wp_json_encode($this->variable);
    }
}
