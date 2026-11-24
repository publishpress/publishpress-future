<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class BooleanResolver implements VariableResolverInterface
{
    /**
     * @var bool
     */
    private $variable;

    public function __construct(bool $variable)
    {
        $this->variable = $variable;
    }

    public function getType(): string
    {
        return 'boolean';
    }

    public function getValue(string $propertyName = '')
    {
        return (bool) $this->variable;
    }

    public function getValueAsString(string $property = ''): string
    {
        return $this->getValue($property) ? __('Yes', 'post-expirator') : __('No', 'post-expirator');
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
        $this->variable = (bool) $value;
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
        return $this->variable ? __('Yes', 'post-expirator') : __('No', 'post-expirator');
    }
}
