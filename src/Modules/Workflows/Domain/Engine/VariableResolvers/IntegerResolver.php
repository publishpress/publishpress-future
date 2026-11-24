<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class IntegerResolver implements VariableResolverInterface
{
    /**
     * @var int
     */
    private $variable;

    public function __construct(int $variable)
    {
        $this->variable = $variable;
    }

    public function getType(): string
    {
        return 'integer';
    }

    public function getValue(string $propertyName = '')
    {
        return (int) $this->variable;
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
     * @inheritDoc
     */
    public function getVariable()
    {
        return $this->variable;
    }

    public function setValue(string $name, $value): void
    {
        $this->variable = (int) $value;
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
