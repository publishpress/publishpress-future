<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

class PostTermsResolver implements VariableResolverInterface
{
    private $post;
    private $termsData = [];
    private $cachedTerms;

    public function __construct($post = null, $cachedTerms = [])
    {
        $this->post = $post;
        $this->cachedTerms = $cachedTerms;

        if (!empty($cachedTerms)) {
            $this->termsData = $cachedTerms;
        } elseif ($post && isset($post->ID)) {
            $this->loadTermsData();
        }
    }

    private function loadTermsData()
    {
        $taxonomies = get_object_taxonomies($this->post->post_type, 'objects');

        foreach ($taxonomies as $taxonomy => $taxonomyObject) {
            $terms = wp_get_post_terms($this->post->ID, $taxonomy);
            $this->termsData[$taxonomy] = $terms;
        }
    }

    public function getType(): string
    {
        return 'terms';
    }

    public function getValue(string $propertyName = '')
    {
        if (empty($propertyName)) {
            return $this->termsData;
        }

        $parts = explode('.', $propertyName);
        $value = $this->termsData;

        foreach ($parts as $part) {
            if (isset($value[$part])) {
                $value = $value[$part];
            } else {
                return '';
            }
        }

        return $value;
    }

    public function getValueAsString(string $propertyName = ''): string
    {
        $value = $this->getValue($propertyName);

        if (is_array($value)) {
            return wp_json_encode($value);
        }

        return (string)$value;
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->termsData
        ];
    }

    public function getVariable()
    {
        return $this->termsData;
    }

    public function setValue(string $name, $value): void
    {
        return;
    }

    public function __isset($name): bool
    {
        return isset($this->termsData[$name]);
    }

    public function __get($name)
    {
        if (isset($this->termsData[$name])) {
            return new TermPropertyResolver($this->termsData[$name]);
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
        return $this->getValueAsString();
    }
}
