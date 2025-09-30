<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

class TermPropertyResolver implements VariableResolverInterface
{
    private $termData;

    public function __construct($termData)
    {
        $this->termData = $termData;
    }

    public function getType(): string
    {
        return 'term_property';
    }

    public function getValue(string $propertyName = '')
    {
        if (empty($propertyName)) {
            return $this->termData;
        }

        if (is_array($this->termData)) {
            $values = [];
            foreach ($this->termData as $term) {
                if ($term instanceof \WP_Term && isset($term->$propertyName)) {
                    $values[] = $term->$propertyName;
                }
            }

            return $values;
        }

        return '';
    }

    public function getValueAsString(string $propertyName = ''): string
    {
        if (empty($propertyName)) {
            return wp_json_encode($this->termData);
        }

        $values = $this->getValue($propertyName);

        if (is_array($values) && !empty($values)) {
            return implode(', ', $values);
        }

        return (string)$values;
    }

    public function setValue(string $propertyName, $value): void
    {
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->termData
        ];
    }

    public function getVariable()
    {
        return $this->termData;
    }

    public function __isset($name): bool
    {
        return isset($this->termData[$name]);
    }

    public function __get($property)
    {
        if (is_array($this->termData)) {
            $values = [];

            foreach ($this->termData as $term) {
                if ($term instanceof \WP_Term) {
                    switch ($property) {
                        case 'name':
                            $values[] = $term->name;
                            break;
                        case 'slug':
                            $values[] = $term->slug;
                            break;
                        case 'term_id':
                            $values[] = $term->term_id;
                            break;
                        case 'count':
                            $values[] = $term->count;
                            break;
                        default:
                            if (isset($term->$property)) {
                                $values[] = $term->$property;
                            }
                            break;
                    }
                }
            }

            return implode(', ', $values);
        }

        return '';
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
        return wp_json_encode($this->termData);
    }
}
