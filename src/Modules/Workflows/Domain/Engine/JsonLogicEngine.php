<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use JWadhams\JsonLogic;
use PublishPress\Future\Modules\Workflows\Interfaces\JsonLogicEngineInterface;

class JsonLogicEngine implements JsonLogicEngineInterface
{
    public function __construct()
    {
        $this->addNewOperations();
    }

    public function apply($expression, $data)
    {
        return JsonLogic::apply($expression, $data);
    }

    public function addOperation($name, $callback)
    {
        JsonLogic::add_operation($name, $callback);
    }

    public function isLogic($expression)
    {
        return JsonLogic::is_logic($expression);
    }

    private function addNewOperations()
    {
        JsonLogic::add_operation('startsWith', [$this, 'operationStartsWith']);
        JsonLogic::add_operation('endsWith', [$this, 'operationEndsWith']);
        JsonLogic::add_operation('contains', [$this, 'operationContains']);
        JsonLogic::add_operation('doesNotContain', [$this, 'operationDoesNotContain']);
        JsonLogic::add_operation('doesNotBeginWith', [$this, 'operationDoesNotBeginWith']);
        JsonLogic::add_operation('doesNotEndWith', [$this, 'operationDoesNotEndWith']);
        JsonLogic::add_operation('null', [$this, 'operationNull']);
        JsonLogic::add_operation('notNull', [$this, 'operationNotNull']);
        JsonLogic::add_operation('in', [$this, 'operationIn']);
        JsonLogic::add_operation('notIn', [$this, 'operationNotIn']);
        JsonLogic::add_operation('between', [$this, 'operationBetween']);
        JsonLogic::add_operation('notBetween', [$this, 'operationNotBetween']);
        JsonLogic::add_operation('isEmpty', [$this, 'operationIsEmpty']);
        JsonLogic::add_operation('isNotEmpty', [$this, 'operationIsNotEmpty']);
    }

    public function operationStartsWith($value, $pattern)
    {
        return strpos((string)$value, $pattern) === 0;
    }

    public function operationEndsWith($value, $pattern)
    {
        return substr((string)$value, -strlen($pattern)) === $pattern;
    }

    public function operationContains($value, $pattern)
    {
        return strpos((string)$value, $pattern) !== false;
    }

    public function operationDoesNotContain($value, $pattern)
    {
        return ! $this->operationContains($value, $pattern);
    }

    public function operationDoesNotBeginWith($value, $pattern)
    {
        return ! $this->operationStartsWith($value, $pattern);
    }

    public function operationDoesNotEndWith($value, $pattern)
    {
        return ! $this->operationEndsWith($value, $pattern);
    }

    public function operationNull($value)
    {
        return $value === null;
    }

    public function operationNotNull($value)
    {
        return $value !== null;
    }

    public function operationIn($value, $array)
    {
        if (is_array($array) && empty($array)) {
            return false;
        }

        if (is_string($array) && strpos($array, ',') !== false) {
            $array = explode(',', $array);
            $array = array_map('trim', $array);
        } elseif (is_string($array)) {
            return $this->operationContains($array, $value);
        }

        if (is_null($array)) {
            return false;
        }

        $found = false;

        foreach ($array as $item) {
            if ((string)$item === $value) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    public function operationNotIn($value, $array)
    {
        return ! $this->operationIn($value, $array);
    }

    public function operationBetween($value, $min, $max)
    {
        return $value >= $min && $value <= $max;
    }

    public function operationNotBetween($value, $min, $max)
    {
        return $value < $min || $value > $max;
    }

    /**
     * Check if the value is empty
     *
     * @param mixed $value
     * @return bool
     * @since 4.9.4
     */
    public function operationIsEmpty($value)
    {
        return empty($value);
    }

    /**
     * Check if the value is not empty
     *
     * @param mixed $value
     * @return bool
     * @since 4.9.4
     */
    public function operationIsNotEmpty($value)
    {
        return ! $this->operationIsEmpty($value);
    }
}
