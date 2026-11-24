<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

/**
 * The walker class for category checklist.
 *
 * @internal
 *
 * @access private
 * @deprecated 3.1.4
 */
// phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class Walker_PostExpirator_Category_Checklist extends Walker
{
    /**
     * What the class handles.
     *
     * @var string
     */
    public $tree_type = 'category';

    /**
     * DB fields to use.
     *
     * @var array
     */
    public $db_fields = ['parent' => 'parent', 'id' => 'term_id']; // TODO: decouple this

    /**
     * The disabled attribute.
     *
     * @var string
     */
    public $disabled = '';

    /**
     * Set the disabled attribute.
     */
    public function setDisabled()
    {
        $this->disabled = 'disabled="disabled"';
    }

    /**
     * Starts the list before the elements are added.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. This method is called at the start of the output list.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param int $depth Depth of the item.
     * @param array $args An array of additional arguments.
     */
    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent<ul class='children'>\n";
    }

    /**
     * Ends the list of after the elements are added.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. This method finishes the list at the end of output of the elements.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param int $depth Depth of the item.
     * @param array $args An array of additional arguments.
     */
    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    /**
     * Start the element output.
     *
     * The $args parameter holds additional values that may be used with the child
     * class methods. Includes the element output also.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param object $data_object The data object for category.
     * @param int $depth Depth of the item.
     * @param array $args An array of additional arguments.
     * @param int $current_object_id ID of the current item.
     */
    public function start_el(&$output, $data_object, $depth = 0, $args = [], $current_object_id = 0)
    {
        $taxonomy = $args['taxonomy'] ?? 'category';
        $popular_cats = isset($args['popular_cats']) ? (array) $args['popular_cats'] : [];
        $selected_cats = isset($args['selected_cats']) ? (array) $args['selected_cats'] : [];

        $container = Container::getInstance();
        $hooks = $container->get(ServicesAbstract::HOOKS);

        $name = 'expirationdate_category';

        $class = in_array($data_object->term_id, $popular_cats, true) ? ' class="expirator-category"' : '';
        $output .= "\n<li id='expirator-{$taxonomy}-{$data_object->term_id}'$class>" . '<label class="selectit"><input value="' . $data_object->term_id . '" type="checkbox" name="' . $name . '[]" id="expirator-in-' . $taxonomy . '-' . $data_object->term_id . '"' . checked(
            in_array($data_object->term_id, $selected_cats, true),
            true,
            false
        ) . disabled(empty($args['disabled']), false, false) . ' ' . $this->disabled . '/> ' . esc_html(
            $hooks->applyFilters('the_category', $data_object->name)
        ) . '</label>';
    }

    /**
     * Ends the element output, if needed.
     *
     * The $args parameter holds additional values that may be used with the child class methods.
     *
     * @param string $output Used to append additional content (passed by reference).
     * @param object $category The data object.
     * @param int $depth Depth of the item.
     * @param array $args An array of additional arguments.
     */
    public function end_el(&$output, $category, $depth = 0, $args = [])
    {
        $output .= "</li>\n";
    }
}
// phpcs:enable
