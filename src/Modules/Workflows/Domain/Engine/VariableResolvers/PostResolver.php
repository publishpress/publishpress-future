<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\VariableResolverInterface;

class PostResolver implements VariableResolverInterface
{
    /**
     * @var object
     */
    private $post;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var string
     */
    private $cachedPermalink;

    /**
     * @var ARRAY
     */
    private $cachedTerms;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    public function __construct(
        object $post,
        HookableInterface $hooks,
        string $cachedPermalink = '',
        \Closure $expirablePostModelFactory = null,
        $cachedTerms = []
    ) {
        $this->post = $post;
        $this->hooks = $hooks;
        $this->cachedPermalink = $cachedPermalink;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->cachedTerms = $cachedTerms;
    }

    public function getType(): string
    {
        return 'post';
    }

    public function getValue(string $property = '')
    {
        if (empty($property)) {
            $property = 'ID';
        }

        switch ($property) {
            case 'ID':
            case 'id':
                return $this->post->ID;

            case 'post_title':
            case 'title':
                return $this->post->post_title;

            case 'post_name':
            case 'slug':
                return $this->post->post_name;

            case 'post_content':
            case 'content':
                return $this->hooks->applyFilters(
                    HooksAbstract::FILTER_THE_CONTENT,
                    $this->post->post_content
                );

            case 'post_content_text':
            case 'content_text':
                return wp_strip_all_tags(
                    strip_shortcodes(
                        $this->hooks->applyFilters(
                            HooksAbstract::FILTER_THE_CONTENT,
                            $this->post->post_content
                        )
                    )
                );

            case 'post_excerpt':
            case 'excerpt':
                return $this->post->post_excerpt;

            case 'post_type':
            case 'type':
                return $this->post->post_type;

            case 'post_status':
            case 'status':
                return $this->post->post_status;

            case 'post_date':
            case 'date':
                return $this->post->post_date;

            case 'post_modified':
            case 'modified':
                return $this->post->post_modified;

            case 'permalink':
                if (! empty($this->cachedPermalink)) {
                    return $this->cachedPermalink;
                }

                return $this->getPermalink($this->post->ID);

            case 'meta':
                return new PostMetaResolver($this->post->ID);

            case 'post_author':
            case 'author':
                return new UserResolver($this->post->post_author);

            case 'terms':
                return new PostTermsResolver($this->post, $this->cachedTerms);

            case 'future':
                return new FutureActionResolver($this->post, $this->expirablePostModelFactory);
        }

        return '';
    }

    public function getValueAsString(string $property = ''): string
    {
        return (string)$this->getValue($property);
    }

    protected function getPermalink($postId)
    {
        return get_permalink($postId);
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
        return $this->post;
    }

    public function setValue(string $name, $value): void
    {
        if ($name === 'id') {
            $this->post->ID = $value;
            return;
        }

        if (isset($this->post->$name)) {
            $this->post->$name = $value;
        }
    }

    public function __isset($name): bool
    {
        return in_array(
            $name,
            [
                'id',
                'ID',
                'post_title',
                'title  ',
                'post_name',
                'slug',
                'post_content',
                'content',
                'post_content_text',
                'content_text',
                'post_excerpt',
                'excerpt',
                'post_type',
                'type',
                'post_status',
                'status',
                'post_date',
                'date',
                'post_modified',
                'modified',
                'permalink',
                'meta',
                'post_author',
                'author',
                'terms',
                'future',
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
        return (string)$this->post->ID;
    }
}
