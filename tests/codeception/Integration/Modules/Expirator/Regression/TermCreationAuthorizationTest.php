<?php

/**
 * Regression tests for unauthorized taxonomy term creation.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Expirator\Regression;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use PublishPress\Future\Modules\Settings\Models\TaxonomiesModel;
use WP_Term;
use WP_User;

/**
 * @group regression
 */
class TermCreationAuthorizationTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @since 4.10.6
     */
    private function getTaxonomiesModel(): TaxonomiesModel
    {
        $factory = Container::getInstance()->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY);

        return $factory();
    }

    /**
     * @since 4.10.6
     */
    private function setCurrentAuthorWhoCanExpirePosts(): int
    {
        $userId = $this->factory()->user->create(
            [
                'role' => 'author',
            ]
        );

        $user = new WP_User($userId);
        $user->add_cap(CapabilitiesAbstract::EXPIRE_POST);

        wp_set_current_user($userId);

        $this->assertTrue(current_user_can(CapabilitiesAbstract::EXPIRE_POST));
        $this->assertTrue(current_user_can('edit_posts'));
        $this->assertFalse(current_user_can('manage_categories'));

        return $userId;
    }

    /**
     * @since 4.10.6
     */
    private function countTerms(string $taxonomy): int
    {
        $count = wp_count_terms(
            [
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
            ]
        );

        $this->assertNotWPError($count);

        return (int) $count;
    }

    /**
     * @since 4.10.6
     */
    public function test_should_not_create_category_when_author_lacks_edit_terms(): void
    {
        $this->setCurrentAuthorWhoCanExpirePosts();

        $termName = 'Attacker Category';
        $this->assertFalse(get_term_by('name', $termName, 'category'));
        $countBefore = $this->countTerms('category');

        $result = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
            'category',
            [$termName],
            'post'
        );

        $this->assertSame([], $result);
        $this->assertFalse(get_term_by('name', $termName, 'category'));
        $this->assertSame($countBefore, $this->countTerms('category'));
    }

    /**
     * @since 4.10.6
     */
    public function test_should_keep_existing_category_name_and_id_when_author_cannot_create(): void
    {
        $existingNameId = $this->factory()->term->create(
            [
                'taxonomy' => 'category',
                'name' => 'Existing Author Category',
            ]
        );
        $numericId = $this->factory()->term->create(
            [
                'taxonomy' => 'category',
                'name' => 'Existing Numeric Category',
            ]
        );

        $this->setCurrentAuthorWhoCanExpirePosts();

        $countBefore = $this->countTerms('category');

        $result = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
            'category',
            ['Existing Author Category', (string) $numericId],
            'post'
        );

        $this->assertSame([(int) $numericId, (int) $existingNameId], $result);
        $this->assertSame($countBefore, $this->countTerms('category'));

        $resolved = get_term_by('name', 'Existing Author Category', 'category');
        $this->assertInstanceOf(WP_Term::class, $resolved);
        $this->assertSame((int) $existingNameId, (int) $resolved->term_id);
    }

    /**
     * @since 4.10.6
     */
    public function test_should_create_category_when_administrator_has_edit_terms(): void
    {
        $adminId = $this->factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        wp_set_current_user($adminId);

        $this->assertTrue(current_user_can('manage_categories'));

        $termName = 'Administrator Created Category';
        $this->assertFalse(get_term_by('name', $termName, 'category'));
        $countBefore = $this->countTerms('category');

        $result = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
            'category',
            [$termName],
            'post'
        );

        $created = get_term_by('name', $termName, 'category');
        $this->assertInstanceOf(WP_Term::class, $created);
        $this->assertSame([(int) $created->term_id], $result);
        $this->assertSame($countBefore + 1, $this->countTerms('category'));
    }

    /**
     * Core post_tag edit_terms is edit_post_tags, which maps to manage_categories.
     * A taxonomy whose edit_terms cap is edit_posts must still allow the author to
     * create a term. That distinguishes the taxonomy cap from a blanket refusal
     * and from manage_terms (manage_categories), which this author does not have.
     *
     * @since 4.10.6
     */
    public function test_should_create_term_when_edit_terms_is_edit_posts_but_not_post_tag(): void
    {
        $taxonomy = 'ppfuture_edit_terms_tax';

        register_taxonomy(
            $taxonomy,
            'post',
            [
                'public' => false,
                'capabilities' => [
                    'manage_terms' => 'manage_categories',
                    'edit_terms' => 'edit_posts',
                    'delete_terms' => 'manage_categories',
                    'assign_terms' => 'edit_posts',
                ],
            ]
        );

        try {
            $this->setCurrentAuthorWhoCanExpirePosts();

            $tagName = 'Author Created Tag';
            $this->assertFalse(current_user_can(get_taxonomy('post_tag')->cap->edit_terms));
            $this->assertFalse(get_term_by('name', $tagName, 'post_tag'));
            $tagCountBefore = $this->countTerms('post_tag');

            $tagResult = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
                'post_tag',
                [$tagName],
                'post'
            );

            $this->assertSame([], $tagResult);
            $this->assertFalse(get_term_by('name', $tagName, 'post_tag'));
            $this->assertSame($tagCountBefore, $this->countTerms('post_tag'));

            $termName = 'Author Created Edit Terms Term';
            $this->assertTrue(current_user_can(get_taxonomy($taxonomy)->cap->edit_terms));
            $this->assertFalse(current_user_can(get_taxonomy($taxonomy)->cap->manage_terms));
            $this->assertFalse(get_term_by('name', $termName, $taxonomy));
            $countBefore = $this->countTerms($taxonomy);

            $result = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
                $taxonomy,
                [$termName],
                'post'
            );

            $created = get_term_by('name', $termName, $taxonomy);
            $this->assertInstanceOf(WP_Term::class, $created);
            $this->assertSame([(int) $created->term_id], $result);
            $this->assertSame($countBefore + 1, $this->countTerms($taxonomy));
        } finally {
            unregister_taxonomy($taxonomy);
        }
    }

    /**
     * @since 4.10.6
     */
    public function test_should_reject_taxonomy_not_registered_for_post_type(): void
    {
        $adminId = $this->factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        wp_set_current_user($adminId);

        $termName = 'Evil Menu';
        $this->assertFalse(is_object_in_taxonomy('post', 'nav_menu'));
        $this->assertFalse(get_term_by('name', $termName, 'nav_menu'));
        $countBefore = $this->countTerms('nav_menu');

        $result = $this->getTaxonomiesModel()->normalizeTermsCreatingIfNecessary(
            'nav_menu',
            [$termName],
            'post'
        );

        $this->assertSame([], $result);
        $this->assertFalse(get_term_by('name', $termName, 'nav_menu'));
        $this->assertSame($countBefore, $this->countTerms('nav_menu'));
    }

    /**
     * @since 4.10.6
     */
    public function test_should_not_insert_category_from_createTermAndReturnId_when_author_cannot_edit_terms(): void
    {
        $this->setCurrentAuthorWhoCanExpirePosts();

        $termName = 'Blocked Category';
        $this->assertFalse(get_term_by('name', $termName, 'category'));
        $countBefore = $this->countTerms('category');

        $result = $this->getTaxonomiesModel()->createTermAndReturnId(
            'category',
            $termName,
            'post'
        );

        $this->assertSame(0, $result);
        $this->assertFalse(get_term_by('name', $termName, 'category'));
        $this->assertSame($countBefore, $this->countTerms('category'));
    }
}
