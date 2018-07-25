=== Bulma Navbar Menu Walker ===
Plugin URI: https://github.com/cdayjr/bulma-navbar-menu-walker
Contributors: cdayjr
Author URI: https://www.chadwadedayjr.info/
Tags: jquery migrate, console message
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 1.0.0
Requires PHP: 7.0.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A Walker class that generates `navbar-menu`-compatible `navbar-item` elements for the Bulma CSS Framework instead of the standard WordPress `li` elements. Remains compatible with WordPress default hooks and keeps default classes as well.

== Description ==

A Walker class that generates menu items formatted for the [Bulma CSS Framework](https://bulma.io) version 0.7.1. For use with your theme. This is just the walker class, so you'll have to do the heavy lifting yourself.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/bulma-navbar-menu-walker` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Update your theme to use this Walker class when it generates menus. Here's an example:
```
$menu_options = [];
try {
  $menu_options['walker'] = new \cdayjr\WordPress\BulmaNavbarMenuWalker\Walker(true, false, true, false);
  $menu_options['container_class'] = 'navbar-menu';
  $menu_options['menu_class'] = 'navbar-end';
  $menu_options['items_wrap'] = '<div id="%1$s" class="%2$s">%3$s</div>';
} catch (\Throwable $t) {
  unset($menu_options['walker']);
  unset($menu_options['container_class']);
  unset($menu_options['menu_class']);
  unset($menu_options['items_wrap']);
}
return wp_nav_menu($menu_options)?:'';
```

The four booleans in the constructor for the walker are as follows:

- is_right
- has_dropdown_up
- is_hoverable
- is_boxed

Each adds the appropriate CSS class to the menu. See the [Bulma Navbar documetnation](https://bulma.io/documentation/components/navbar/) for more information on these and what they do.

You can omit them as well and it'll use the defaults (all false except for `is-hoverable`).

Because Bulma only has two depths for the menu (initial and a dropdown) additional depths are just added via a separator.

== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

No upgrades yet.

== Acknowledgements ==

* [Bulma](https://bulma.io/) - A great CSS framework.

