<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;

\defined('ABSPATH') || exit;

class FutureActionResolver implements VariableResolverInterface
{
    /**
     * @var object
     */
    private $post;

    /**
     * @var ExpirablePostModel
     */
    private $postModel;

    private $futureActionCache = [];

    public function __construct(object $post, \Closure $postModelFactory)
    {
        $this->post = $post;
        $this->postModel = $postModelFactory($post->ID);
    }

    public function getType(): string
    {
        return 'future_action';
    }

    public function getValue(string $property = '')
    {
        if (empty($property)) {
            $property = 'enabled';
        }

        if (isset($this->futureActionCache[$property])) {
            return $this->futureActionCache[$property];
        }

        switch ($property) {
            case 'enabled':
                $value = $this->getPropertyEnabled();
                $this->futureActionCache[$property] = $value;
                return $value;

            case 'action':
                $value = $this->getPropertyAction();
                $this->futureActionCache[$property] = $value;
                return $value;

            case 'date':
                $value = $this->getPropertyDate();
                $this->futureActionCache[$property] = $value;
                return $value;

            case 'date_string':
                $value = $this->getPropertyDateString();
                $this->futureActionCache[$property] = $value;
                return $value;

            case 'terms':
                $value = new TermsArrayResolver($this->getPropertyTerms());
                $this->futureActionCache[$property] = $value;
                return $value;

            case 'new_status':
                $value = $this->getPropertyNewStatus();
                $this->futureActionCache[$property] = $value;
                return $value;
        }

        return '';
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string) $this->getValue($property);
    }

    public function compact(): array
    {
        return [
            'type' => $this->getType(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getVariable()
    {
        return [
            'enabled' => $this->getPropertyEnabled(),
            'action' => $this->getPropertyAction(),
            'date' => $this->getPropertyDate(),
            'date_string' => $this->getPropertyDateString(),
            'terms' => $this->getPropertyTerms(),
            'new_status' => $this->getPropertyNewStatus(),
        ];
    }

    public function setValue(string $name, $value): void
    {
        $this->futureActionCache[$name] = $value;
    }

    public function __isset($name): bool
    {
        return in_array(
            $name,
            [
                'enabled',
                'action',
                'date',
                'date_string',
                'terms',
                'new_status',
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
        return $this->getPropertyEnabled() ? '1' : '0';
    }

    private function getPropertyEnabled(): bool
    {
        return $this->postModel->isExpirationEnabled();
    }

    private function getPropertyAction(): string
    {
        return $this->postModel->getExpirationAction();
    }

    private function getPropertyDate(): int
    {
        return $this->postModel->getExpirationDateAsUnixTime();
    }

    private function getPropertyDateString(): string
    {
        return $this->postModel->getExpirationDateString(false);
    }

    private function getPropertyTerms(): array
    {
        $action = (string) $this->postModel->getExpirationAction();

        $categoryRelated = [
            ExpirationActionsAbstract::POST_CATEGORY_SET,
            ExpirationActionsAbstract::POST_CATEGORY_ADD,
            ExpirationActionsAbstract::POST_CATEGORY_REMOVE,
        ];

        if (in_array($action, $categoryRelated)) {
            $terms = $this->postModel->getExpirationCategoryIDs();

            $labels = array_map(function ($term) {
                return get_term($term)->name;
            }, $terms);

            return [
                'ids' => $terms,
                'labels' => $labels,
            ];
        }

        return [
            'ids' => [],
            'labels' => [],
        ];
    }

    private function getPropertyNewStatus(): string
    {
        $action = (string) $this->postModel->getExpirationAction();

        $statusRelated = [
            ExpirationActionsAbstract::CHANGE_POST_STATUS,
            ExpirationActionsAbstract::POST_STATUS_TO_DRAFT,
            ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE,
            ExpirationActionsAbstract::POST_STATUS_TO_TRASH,
        ];

        if (in_array($action, $statusRelated)) {
            return $this->postModel->getExpirationNewStatus();
        }

        return '';
    }
}
