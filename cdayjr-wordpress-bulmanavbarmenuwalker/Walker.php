<?php
/**
* BulmaWalker is a WordPress menu Walker implementation for Bulma-based menus
*
* Bulma uses a specific format for its navbar menu that doesn't line up
* with how WordPress does menus by default. This class tells WordPress
* to generate the right code to use with Bulma.
*
* Example usage:
* ```
*       $menu_options = [];
*       try {
*           $menu_options['walker'] = new \cdayjr\WordPress\BulmaNavbarMenuWalker\Walker(true, false, true, false);
*           $menu_options['container_class'] = 'navbar-menu';
*           $menu_options['menu_class'] = 'navbar-end';
*           $menu_options['items_wrap'] = '<div id="%1$s" class="%2$s">%3$s</div>';
*       } catch (\Throwable $t) {
*           unset($menu_options['walker']);
*           unset($menu_options['container_class']);
*           unset($menu_options['menu_class']);
*           unset($menu_options['items_wrap']);
*       }
*       return wp_nav_menu($menu_options)?:'';
* ```
*
* See these WordPress articles for reference:
* * https://codex.wordpress.org/Class_Reference/Walker
* * https://core.trac.wordpress.org/browser/tags/4.9.7/src//wp-includes/class-walker-nav-menu.php
*
*/
namespace cdayjr\WordPress\BulmaNavbarMenuWalker;

use \Walker as AbstractWalker;

class Walker extends AbstractWalker
{

    /**
     * Use parent and id values from the database
     *
     * @var array
     *
     * @see Walker::$db_fields
     */
    public $db_fields = [
        'parent' => 'menu_item_parent',
        'id'     => 'db_id'
    ];

    /**
     * If dropdowns should be aligned to the right.
     * See https://bulma.io/documentation/components/navbar/ for info.
     *
     * @var boolean
     */
    private $is_right = false;

    /**
     * If dropdowns should go up instead of down.
     * See https://bulma.io/documentation/components/navbar/ for info.
     *
     * @var boolean
     */
    private $has_dropdown_up = false;

    /**
     * If dropdowns should activate on hover.
     * See https://bulma.io/documentation/components/navbar/ for info.
     *
     * By default this is on since we'll assume you don't have JS set up
     * to toggle active/inactive state.
     *
     * @var boolean
     */
    private $is_hoverable = true;

    /**
     * If dropdowns should be considered boxed.
     * See https://bulma.io/documentation/components/navbar/ for info.
     *
     * @var boolean
     */
    private $is_boxed = false;



    /**
     * Constructor function, lets us tell bulma some configuration details
     *
     * @param boolean $is_right sets the $is_right property,
     * see that for details
     * @param boolean $has_dropdown_up sets the $has_dropdown_up property,
     * see that for details
     * @param boolean $is_hoverable sets the $is_hoverable property,
     * see that for details
     * @param boolean $is_boxed sets the $is_boxed property,
     * see that for details
     */
    public function __construct(
        bool $is_right = false,
        bool $has_dropdown_up = false,
        bool $is_hoverable = true,
        bool $is_boxed = false
    ) {
        $this->is_right = $is_right;
        $this->has_dropdown_up = $has_dropdown_up;
        $this->is_hoverable = $is_hoverable;
        $this->is_boxed = $is_boxed;
    }

    /**
     * Opening HTML for starting a new depth level in this menu HTML.
     *
     * @param string &$output The output HTML string
     * @param int $depth The menu depth so far
     * @param array $arguments Menu arguments passed
     *
     * @return void
     */
    public function start_lvl(&$output, $depth = 0, $arguments = [])
    {
        if (0 === $depth) {
            $dropdownClasses = [
                'navbar-dropdown'
            ];
            if ($this->is_right) {
                $dropdownClasses[] = 'is-right';
            }
            if ($this->is_boxed) {
                $dropdownClasses[] = 'is-boxed';
            }
            $dropdownClasses = esc_attr(join(' ', $dropdownClasses));
            $output .= '<div class="'.$dropdownClasses.'">';
        } else {
            // Bulma doesn't support multi-level menus past the first dropdown
            // So we'll just put a divider there.
            $output .= '<hr class="navbar-divider">';
        }
    }

    /**
     * Closing HTML for starting a new depth level in this menu HTML.
     *
     * @param string &$output The output HTML string
     * @param int $depth The menu depth so far
     * @param array $arguments Menu arguments passed
     *
     * @return void
     */
    public function end_lvl(&$output, $depth = 0, $arguments = [])
    {
        if (0 === $depth) {
            $output .= '</div>';
        }
    }

    /**
     * HTML for an element in this menu HTML.
     *
     * @param string &$output The output HTML string
     * @param WP_Post $item The manu item object
     * @param int $depth The menu depth so far
     * @param array $arguments Menu arguments passed
     * @param int $id The page ID of the item object
     *
     * @return void
     */
    public function start_el(&$output, $item, $depth = 0, $arguments = [], $current_item_id = 0)
    {
        if (property_exists($item, 'object_id')) {
            $id = (int) $item->object_id;
        } elseif (property_exists($item, 'ID')) {
            // revert to menu item ID I guess?
            $id = (int) $item->ID;
        } else {
            $id = 0;
        }

        if (property_exists($item, 'ID')) {
            $menu_item_id = (int) $item->ID;
        } else {
            $menu_item_id = 0;
        }

        if (property_exists($item, 'title')) {
            $title = $item->title;
        } else {
            // Can't do anything with no title...
            return;
        }

        // Get additional arguments from `nav_menu_item_args` filter
        $arguments = apply_filters('nav_menu_item_args', $arguments, $item, $depth);

        $tab = '';
        if (!(property_exists($arguments, 'item_spacing') &&
            'discard' === $arguments->item_spacing) ||
            !(isset($arguments['item_spacing']) &&
            'discard' === $arguments['item_spacing'])) {
            $tab = "\t";
        }
        $indent = ($depth)?str_repeat($tab, $depth):'';

        // load default classes
        if (property_exists($item, 'classes')) {
            $cssClasses = (array)$item->classes;
        } else {
            $cssClasses = [];
        }

        // Check if item has children
        $hasChildren = in_array('menu-item-has-children', $cssClasses);

        // WordPress default for menu item
        if (0 !== $menu_item_id) {
            $cssClasses[] = 'menu-item-'.$menu_item_id;
        }

        // items with children have a different class
        if ($hasChildren && 0 === $depth) {
            $cssClasses[] = 'navbar-link';
        } else {
            $cssClasses[] = 'navbar-item';
        }

        // Check if the current item is the active page
        if ((int)get_the_ID() === $id
            || (
            1 === (int)get_the_ID() &&
            (int)get_option('page_for_posts') === $id
            ) || (
                is_archive() &&
                property_exists($item, 'type') &&
                property_exists($item, 'object') &&
                "post_type_archive" === $item->type &&
                is_post_type_archive($item->object)
            )) {
            $cssClasses[] = 'is-active';
        }

        // Get CSS classes from `nav_menu_css_class` filter
        $cssClasses = apply_filters(
            'nav_menu_css_class',
            $cssClasses,
            $item,
            $arguments,
            $depth
        );

        $cssClasses = join(' ', $cssClasses);

        // Get element ID from `nav_menu_item_id` filter
        if (0 !== $menu_item_id) {
            $elementId = apply_filters(
                'nav_menu_item_id',
                (0 !== $menu_item_id)?'menu-item-'.$menu_item_id:'',
                $item,
                $arguments,
                $depth
            );
        }

        // HTML Attributes
        $attributes = [
            'class' => $cssClasses,
        ];

        // Some default attributes from the item
        if (property_exists($item, 'url') && !empty($item->url)) {
            $attributes['href'] = $item->url;
        }
        if (property_exists($item, 'attr_title') && !empty($item->attr_title)) {
            $attributes['title'] = $item->attr_title;
        }
        if (property_exists($item, 'target') && !empty($item->target)) {
            $attributes['target'] = $item->target;
        }
        if (property_exists($item, 'xfn') && !empty($item->xfn)) {
            $attributes['rel'] = $item->xfn;
        }

        // The element ID we may or may not have grabbed earlier
        if ($elementId) {
            $attributes['id'] = $elementId;
        }

        // Get additional attributes from `nav_menu_link_attributes` filter.
        $attributes = apply_filters('nav_menu_link_attributes', $attributes, $item, $arguments, $depth);

        // Format the item title with `the_title` and `nav_menu_item_title` filters.
        $title = apply_filters('the_title', $title, $id);
        $title = apply_filters('nav_menu_item_title', $title, $item, $arguments, $depth);

        if (isset($attributes['href']) && !empty($attributes['href'])) {
            $element = 'a';
        } else {
            $element = 'div';
        }

        $html = $indent;
        if ($hasChildren && 0 === $depth) {
            $dropdownClasses = [
                'navbar-item',
                'has-dropdown',
            ];
            if ($this->has_dropdown_up) {
                $dropdownClasses[] = 'has-dropdown-up';
            }
            if ($this->is_hoverable) {
                $dropdownClasses[] = 'is-hoverable';
            }
            $dropdownClasses = esc_attr(join(' ', $dropdownClasses));
            $html .= '<div class="'.$dropdownClasses.'">';
        }
        $html .= $arguments->before.'<'.$element.' ';
        foreach ($attributes as $attribute=>$value) {
            $attribute = esc_html($attribute);
            if ($attribute === 'href') {
                $value = esc_url($value);
            } else {
                $value = esc_attr($value);
            }
            $html .= $attribute.'="'.$value.'" ';
        }
        $html .= '>';
        $html .= $arguments->link_before.esc_html($title).$arguments->link_after;
        $html .= '</'.$element.'>'.$arguments->after;

        // Format the start output with the `walker_nav_menu_start_el` filter
        $output .= apply_filters('walker_nav_menu_start_el', $html, $item, $depth, $arguments);
    }

    /**
     * Closing HTML for an element in this menu HTML.
     *
     * @param string &$output The output HTML string
     * @param object $item The manu item object
     * @param int $depth The menu depth so far
     * @param array $arguments Menu arguments passed
     *
     * @return void
     */
    public function end_el(&$output, $item, $depth = 0, $arguments = [])
    {
        $newline = '';
        if (!(property_exists($arguments, 'item_spacing') &&
            'discard' === $arguments->item_spacing) ||
            !(isset($arguments['item_spacing']) &&
            'discard' === $arguments['item_spacing'])) {
            $newline = PHP_EOL;
        }

        $hasChildren = property_exists($item, 'classes') &&
            in_array('menu-item-has-children', $item->classes);

        if ($hasChildren && 0 === $depth) {
            $output .= '</div>';
        }

        $output .= $newline;
    }
}
