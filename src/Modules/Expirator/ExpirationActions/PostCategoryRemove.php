<?php

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Framework\WordPress\Models\TermsModel;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;

\defined('ABSPATH') || exit;

class PostCategoryRemove implements ExpirationActionInterface
{
    use TaxonomyRelatedTrait;

    public const SERVICE_NAME = 'expiration.actions.post_category_remove';

    /**
     * @var ExpirablePostModel
     */
    private $postModel;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\ErrorFacade
     */
    private $errorFacade;

    /**
     * @var array
     */
    private $log = [];

    /**
     * @param ExpirablePostModel $postModel
     * @param \PublishPress\Future\Framework\WordPress\Facade\ErrorFacade $errorFacade
     */
    public function __construct($postModel, $errorFacade)
    {
        $this->postModel = $postModel;
        $this->errorFacade = $errorFacade;
    }

    public function __toString()
    {
        return ExpirationActionsAbstract::POST_CATEGORY_REMOVE;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationText()
    {
        if (empty($this->log)) {
            return sprintf(
                // translators: %s is the post type singular label
                __('No terms were removed from the %s.', 'post-expirator'),
                strtolower($this->postModel->getPostTypeSingularLabel())
            );
        } elseif (isset($this->log['error'])) {
            return $this->log['error'];
        }

        $termsModel = new TermsModel();

        return sprintf(
            // translators: %1$s is the taxonomy name, %2$s is the post type singular label, %3$s is the removed terms list, %4$s is the updated terms list
            __(
                'The following terms (%1$s) were removed from the %2$s: %3$s. The new list of terms on the post is: %4$s.',
                'post-expirator'
            ),
            $this->log['expiration_taxonomy'],
            strtolower($this->postModel->getPostTypeSingularLabel()),
            $termsModel->getTermNamesByIdAsString($this->log['removed_terms'], $this->log['expiration_taxonomy']),
            $termsModel->getTermNamesByIdAsString($this->log['updated_terms'], $this->log['expiration_taxonomy'])
        );
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $expirationTaxonomy = $this->postModel->getExpirationTaxonomy();
        $originalTerms = $this->postModel->getTermIDs($expirationTaxonomy);
        $termsToRemove = $this->postModel->getExpirationCategoryIDs();

        $updatedTerms = array_diff($originalTerms, $termsToRemove);

        $removedTerms = array_intersect($originalTerms, $termsToRemove);

        $result = $this->postModel->setTerms($updatedTerms, $expirationTaxonomy);

        $resultIsError = $this->errorFacade->isWpError($result);

        if (! $resultIsError) {
            $this->log = [
                'expiration_taxonomy' => $expirationTaxonomy,
                'original_terms' => $originalTerms,
                'removed_terms' => $removedTerms,
                'updated_terms' => $updatedTerms,
            ];
        } else {
            $this->log['error'] = $result->get_error_message();
        }

        return ! $resultIsError;
    }

    public static function getLabel(string $postType = ''): string
    {
        // translators: %s is the taxonomy name (plural)
        $label = __('Remove selected %s', 'post-expirator');

        if (! empty($postType)) {
            $taxonomy = self::getTaxonomyLabel($postType);

            $label = sprintf($label, $taxonomy);
        }

        return $label;
    }

    public function getDynamicLabel($postType = '')
    {
        return self::getLabel($postType);
    }
}
