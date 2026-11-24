<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class StringResolver implements VariableResolverInterface
{
    /**
     * @var string
     */
    private $variable;

    public function __construct(string $variable)
    {
        $this->variable = $variable;
    }

    public function getType(): string
    {
        return 'string';
    }

    public function getValue(string $propertyName = '')
    {
        return (string) $this->variable;
    }

    public function getValueAsString(string $property = ''): string
    {
        return $this->getValue($property);
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->variable,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariable()
    {
        return $this->variable;
    }

    public function setValue(string $name, $value): void
    {
        $this->variable = $value;
    }

    public function __isset($name): bool
    {
        return false;
    }

    public function __get($name)
    {
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
        return (string) $this->variable;
    }
}
