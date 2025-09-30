<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Models;

use PublishPress\Future\Framework\WordPress\Exceptions\NonexistentTermException;

defined('ABSPATH') or die('Direct access not allowed.');

class TermModel
{
    /**
     * @var int
     */
    private $termId;

    /**
     * @var \WP_Term;
     */
    private $termInstance;

    /**
     * @param int|\WP_Term $term
     */
    public function __construct($term)
    {
        if (is_object($term)) {
            $this->termInstance = $term;
            $this->termId = $term->term_id;
        }

        if (is_numeric($term)) {
            $this->termId = (int)$term;
        }
    }

    /**
     * @return \WP_Term
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentTermException
     */
    public function getTermInstance()
    {
        if (empty($this->termInstance)) {
            $this->termInstance = get_term($this->termId);

            if (! is_object($this->termInstance) || is_wp_error($this->termInstance)) {
                throw new NonexistentTermException();
            }
        }

        return $this->termInstance;
    }

    /**
     * @return bool
     */
    public function termExists()
    {
        try {
            $instance = $this->getTermInstance();

            return is_object($instance);
        } catch (NonexistentTermException $e) {
            return false;
        }
    }

    public function getTermID()
    {
        return (int)$this->termId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        try {
            $term = $this->getTermInstance();

            return $term->name;
        } catch (NonexistentTermException $e) {
            return '';
        }
    }
}
