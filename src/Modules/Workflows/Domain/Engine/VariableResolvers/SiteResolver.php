<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

\defined('ABSPATH') || exit;

class SiteResolver implements VariableResolverInterface
{
    private $siteDataCache = [];

    public function getType(): string
    {
        return 'site';
    }

    public function getValue(string $propertyName = '')
    {
        if (empty($propertyName)) {
            $propertyName = 'name';
        }

        if (isset($this->siteDataCache[$propertyName])) {
            return $this->siteDataCache[$propertyName];
        }

        switch ($propertyName) {
            case 'id':
            case 'ID':
                $value = $this->getSiteId();
                $this->siteDataCache[$propertyName] = $value;
                return $value;

            case 'name':
                $value = $this->getSiteName();
                $this->siteDataCache[$propertyName] = $value;
                return $value;

            case 'description':
                $value = $this->getSiteDescription();

                // no break
            case 'url':
                $value = $this->getSiteUrl();
                $this->siteDataCache[$propertyName] = $value;
                return $value;

            case 'home_url':
                $value = $this->getHomeUrl();
                $this->siteDataCache[$propertyName] = $value;
                return $value;

            case 'admin_email':
                $value = $this->getAdminEmail();
                $this->siteDataCache[$propertyName] = $value;
                return $value;
        }

        return '';
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string) $this->getValue($property);
    }

    protected function getSiteName()
    {
        return get_option('blogname');
    }

    protected function getSiteDescription()
    {
        return get_option('blogdescription');
    }

    protected function getSiteUrl()
    {
        return get_site_url();
    }

    protected function getHomeUrl()
    {
        return get_home_url();
    }

    protected function getAdminEmail()
    {
        return get_option('admin_email');
    }

    protected function getSiteId(): int
    {
        return get_current_blog_id();
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->getValue('name'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariable()
    {
        return $this->getValue();
    }

    public function setValue(string $name, $value): void
    {
        $this->siteDataCache[$name] = $value;
    }

    public function __isset($name): bool
    {
        return in_array(
            $name,
            [
                'id',
                'ID',
                'name',
                'description',
                'url',
                'home_url',
                'admin_email',
                'meta',
            ]
        );
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->getValue($name);
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
        return $this->getSiteName();
    }
}
