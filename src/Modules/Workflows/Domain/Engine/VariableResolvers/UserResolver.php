<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

use function wp_json_encode;

\defined('ABSPATH') || exit;

class UserResolver implements VariableResolverInterface
{
    /**
     * @var object
     */
    private $user;

    public function __construct($user)
    {
        if (is_object($user)) {
            $this->user = $user;
        } elseif (is_numeric($user)) {
            $this->user = get_user_by('ID', $user);
        } else {
            $this->user = null;
        }
    }

    public function getType(): string
    {
        return 'user';
    }

    public function getValue(string $property = '')
    {
        if (empty($this->user)) {
            return '';
        }

        if (empty($property)) {
            $property = 'ID';
        }

        switch ($property) {
            case 'id':
            case 'ID':
                return $this->user->ID;

            case 'user_login':
            case 'login':
                return $this->user->user_login;

            case 'user_email':
            case 'email':
                return $this->user->user_email;

            case 'roles':
                return $this->user->roles;

            case 'caps':
                return $this->user->caps;

            case 'display_name':
                return $this->user->display_name;

            case 'registered':
                return $this->user->user_registered;

            case 'meta':
                return new UserMetaResolver($this->user->ID);
        }

        return '';
    }

    public function getValueAsString($property = ''): string
    {
        $value = $this->getValue($property);

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return (string) $value;
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
            'value' => $this->getValue('id'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariable()
    {
        return $this->user;
    }

    public function setValue(string $name, $value): void
    {
        if (isset($this->user->$name)) {
            $this->user->$name = $value;
        }

        return;
    }

    public function __isset($name): bool
    {
        return in_array(
            $name,
            [
                'id',
                'ID',
                'user_login',
                'login',
                'user_email',
                'email',
                'roles',
                'caps',
                'display_name',
                'registered',
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
        return wp_json_encode($this->user);
    }
}
