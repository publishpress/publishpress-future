<?php

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Framework\WordPress\Models\TermsModel;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\ExpirationActionInterface;
use PublishPress\Future\Modules\Expirator\Models\PostTypeDefaultDataModelFactory;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;

\defined('ABSPATH') || exit;

class PostCategoryAdd implements ExpirationActionInterface
{
    use TaxonomyRelatedTrait;

    public const SERVICE_NAME = 'expiration.actions.post_category_add';

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
     * @var string
     */
    private $taxonomy;

    /**
     * @param ExpirablePostModel $postModel
     * @param \PublishPress\Future\Framework\WordPress\Facade\ErrorFacade $errorFacade
     * @param PostTypeDefaultDataModelFactory $postTypeDefaultDataModelFactory
     */
    public function __construct($postModel, $errorFacade, $postTypeDefaultDataModelFactory)
    {
        $this->postModel = $postModel;
        $this->errorFacade = $errorFacade;

        $this->taxonomy = $postTypeDefaultDataModelFactory->create($postModel->getPostType())->getTaxonomy();
    }

    public function __toString()
    {
        return ExpirationActionsAbstract::POST_CATEGORY_ADD;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationText()
    {
        if (empty($this->log)) {
            return sprintf(
                // translators: %s is the post type singular label
                __('No terms were added to the %s.', 'post-expirator'),
                strtolower($this->postModel->getPostTypeSingularLabel())
            );
        } elseif (isset($this->log['error'])) {
            return $this->log['error'];
        }

        $termsModel = new TermsModel();

        return sprintf(
            // phpcs:ignore Generic.Files.LineLength.TooLong
            // translators: %1$s is the taxonomy label, %2$s is the post type singular label, %3$s is the list of terms added, %4$s is the list of terms on the post
            __(
                'The following terms (%1$s) were added to the %2$s: "%3$s". The full list of terms on the post is: %4$s.', // phpcs:ignore Generic.Files.LineLength.TooLong
                'post-expirator'
            ),
            $this->log['expiration_taxonomy'],
            $termsModel->getTermNamesByIdAsString($this->log['terms_added'], $this->log['expiration_taxonomy']),
            strtolower($this->postModel->getPostTypeSingularLabel()),
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
        $termsToAdd = $this->postModel->getExpirationCategoryIDs();

        $updatedTerms = array_merge($originalTerms, $termsToAdd);

        $result = $this->postModel->setTerms($updatedTerms, $expirationTaxonomy);

        $resultIsError = $this->errorFacade->isWpError($result);

        if (! $resultIsError) {
            $this->log = [
                'expiration_taxonomy' => $expirationTaxonomy,
                'original_terms' => $originalTerms,
                'terms_added' => $termsToAdd,
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
        $label = __('Add extra %s', 'post-expirator');

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
