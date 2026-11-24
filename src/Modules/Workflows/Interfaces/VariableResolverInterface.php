<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface VariableResolverInterface
{
    public function getType(): string;

    public function getValue(string $propertyName = '');

    public function getValueAsString(string $propertyName = ''): string;

    public function setValue(string $propertyName, $value): void;

    public function compact(): array;

    /**
     * @return mixed
     */
    public function getVariable();

    public function __isset($name): bool;

    public function __get($name);

    public function __set($name, $value): void;

    public function __unset($name): void;

    public function __toString(): string;
}
