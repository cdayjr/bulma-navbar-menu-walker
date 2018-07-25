<?php
/**
 * Bulma Navbar Menu Walker
 *
 * @author      Chad Wade Day, Jr.
 * @copyright   2018 Chad Wade Day, Jr.
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Bulma Navbar Menu Walker
 * Plugin URI:  https://github.com/cdayjr/bulma-navbar-menu-walker
 * Description: A Walker class that generates `navbar-menu`-compatible `navbar-item` elements for the Bulma CSS Framework instead of the standard WordPress `li` elements. Remains compatible with WordPress default hooks and keeps default classes as well.
 * Version:     1.0.0
 * Author:      Chad Wade Day, Jr.
 * Author URI:  https://www.chadwadedayjr.info
 * Text Domain: bulma-navbar-menu-walker
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

namespace cdayjr\WordPress\BulmaNavbarMenuWalker;

// Do not access this directly.
defined('ABSPATH') or die('No direct script access allowed.');

// Autoload function based on https://www.php-fig.org/psr/psr-4/examples/
spl_autoload_register(function (string $class_string) {
    // namespace prefix for plugin classes
    $namespace_prefix = 'cdayjr\WordPress\BulmaNavbarMenuWalker';

    // Directory for classes that match the namespace prefix
    $namespace_dir =
        plugin_dir_path(__FILE__).'cdayjr-wordpress-bulmanavbarmenuwalker';

    // Are we looking at a class of this prefix?
    $namespace_prefix_length = mb_strlen($namespace_prefix);
    if (0 !==
        strncmp($namespace_prefix, $class_string, $namespace_prefix_length)) {
        // This is not a class of this prefix, so don't use this autoloader
        return;
    }

    // Get the class without the prefix
    $prefixless_class_name =
        mb_substr($class_string, $namespace_prefix_length);

    // Get the full path to the class file based on
    // the namespace and class name.
    $file = $namespace_dir.
        str_replace('\\', DIRECTORY_SEPARATOR, $prefixless_class_name).'.php';

    // One final check to make sure the file exists
    if (file_exists($file)) {
        require_once $file;
    }
});
