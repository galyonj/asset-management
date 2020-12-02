<?php

/**
 * @link              https://github.com/galyonj
 * @since             1.0.1
 * @package           Coe_Am
 *
 * Plugin Name:       Asset Management
 * Plugin URI:        https://github.com/galyonj/coe-asset-management
 * Description:       Create an Assets custom post type and provide tools for managing Assets taxonomy and metadata.
 * Version:           1.0.1
 * Author:            John Galyon
 * Author URI:        https://github.com/galyonj
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       coe-asset-management
 * Domain Path:       /languages
 *
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see <http://www.gnu.org/licenses/>.
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die( 'That\'s illegal.' );

/**
 * Define global constants
 */
$plugin_data = get_file_data(
	__FILE__,
	array(
		'name'    => 'Plugin Name',
		'version' => 'Version',
		'text'    => 'Text Domain',
	)
);
function coe_am_define_constants( $constant_name, $value ) {
	$constant_name = 'COE_AM_' . $constant_name;
	if ( ! defined( $constant_name ) ) {
		define( $constant_name, $value );
	}
}
coe_am_define_constants( 'FILE', __FILE__ );
coe_am_define_constants( 'DIR', dirname( plugin_basename( __FILE__ ) ) );
coe_am_define_constants( 'BASE', plugin_basename( __FILE__ ) );
coe_am_define_constants( 'URL', plugin_dir_url( __FILE__ ) );
coe_am_define_constants( 'PATH', plugin_dir_path( __FILE__ ) );
coe_am_define_constants( 'SLUG', dirname( plugin_basename( __FILE__ ) ) );
coe_am_define_constants( 'NAME', $plugin_data['name'] );
coe_am_define_constants( 'VERSION', $plugin_data['version'] );
coe_am_define_constants( 'TEXT', $plugin_data['text'] );
coe_am_define_constants( 'PREFIX', 'coe_am_' );
coe_am_define_constants( 'SETTINGS', 'coe_am_' );

/**
 * A useful function that returns an array with the contents of plugin constants
 */
function coe_am_populate_constants() {
	$array = array(
		'file'     => COE_AM_FILE,
		'dir'      => COE_AM_DIR,
		'base'     => COE_AM_BASE,
		'url'      => COE_AM_URL,
		'path'     => COE_AM_PATH,
		'slug'     => COE_AM_SLUG,
		'name'     => COE_AM_NAME,
		'version'  => COE_AM_VERSION,
		'text'     => COE_AM_TEXT,
		'prefix'   => COE_AM_PREFIX,
		'settings' => COE_AM_SETTINGS,
	);
	return $array;
}

/**
 * Put value of plugin constants into an array for easier access
 */
$_coe = coe_am_populate_constants();

/**
 * Register the plugin's text domain
 *
 * @since 1.0.0
 * @internal
 */
function coe_am_load_textdomain() {
	$_coe = coe_am_populate_constants();
	load_plugin_textdomain( $_coe['text'] );
}
add_action( 'plugins_loaded', 'coe_am_load_textdomain' );

function coe_am_enqueue_styles() {
	$_coe = coe_am_populate_constants();

	wp_enqueue_style( 'bootstrap-css', $_coe['url'] . 'assets/bootstrap.min.css' );
}
add_action( 'admin_enqueue_scripts', 'coe_am_enqueue_styles' );

/**
 * Create the submenu for our plugin and post type
 * on the admin dashboard.
 *
 * @since 1.0.0
 */
function coe_am_create_submenu() {
	$_coe        = coe_am_populate_constants();
	$capability  = apply_filters( 'coe_am_required_caps', 'manage_options' );
	$parent_path = 'edit.php?post_type=asset';

	add_submenu_page( $parent_path, __( 'Manage Metadata', $_coe['text'] ), __( 'Manage Metadata', $_coe['text'] ), $capability, 'metadata', 'coe_am_metadata_html' );
	//add_submenu_page( $parent_path, __( 'Help', $_coe['text'] ), __( 'Help', $_coe['text'] ), $capability, 'help', 'display_help' );
}
add_action( 'admin_menu', 'coe_am_create_submenu' );

require_once $_coe['path'] . 'classes/class-coe-am-admin-ui.php';
require_once $_coe['path'] . 'inc/utils.php';
require_once $_coe['path'] . 'inc/register-cpt.php';
require_once $_coe['path'] . 'inc/metadata.php';
require_once $_coe['path'] . 'inc/metaboxes.php';

/**
 * Create a custom option to hold our taxonomy data for later
 *
 * @since 1.0.0
 * @return mixed multidimensional array of taxonomy values
 */
// function coe_am_add_tax_option( $arr = array() ) {
// 	add_option( 'coe_am_metadata', array(), '', 'yes' );
// 	delete_option( 'coe_am_metadata' );
// }
// add_action( 'plugins_loaded', 'coe_am_add_tax_option' );

/**
 * Register our users' custom taxonomies.
 *
 * @since 0.5.0
 *
 * @internal
 */
function coe_am_create_custom_taxonomies() {
	$taxes = get_option( 'coe_am_metadata' );

	/**
	 * Fires before the start of the taxonomy registrations.
	 *
	 * @since 1.0.0
	 * @param array $taxes Array of taxonomies to register.
	 */
	do_action( 'coe_am_pre_register_taxonomies', $taxes );

	if ( ! empty( $taxes ) && is_array( $taxes ) ) {
		foreach ( $taxes as $tax ) {
			/**
			 * Filters whether or not to skip registration of the current iterated taxonomy.
			 *
			 * Dynamic part of the filter name is the chosen taxonomy slug.
			 *
			 * @since 1.7.0
			 *
			 * @param bool  $value Whether or not to skip the taxonomy.
			 * @param array $tax   Current taxonomy being registered.
			 */
			if ( (bool) apply_filters( "coe_am_disable_{$tax['name']}_tax", false, $tax ) ) {
				continue;
			}

			/**
			 * Filters whether or not to skip registration of the current iterated taxonomy.
			 *
			 * @since 1.7.0
			 *
			 * @param bool  $value Whether or not to skip the taxonomy.
			 * @param array $tax   Current taxonomy being registered.
			 */
			if ( (bool) apply_filters( 'coe_am_disable_tax', false, $tax ) ) {
				continue;
			}

			coe_am_register_single_taxonomy( $tax );
		}
	} else {
		return;
	}

	/**
	 * Fires after the completion of the taxonomy registrations.
	 *
	 * @since 1.0.0
	 * @param array $taxes Array of taxonomies registered.
	 */
	do_action( 'coe_am_post_register_taxonomies', $taxes );
}
add_action( 'init', 'coe_am_create_custom_taxonomies', 9 );  // Leave on standard init for legacy purposes.

function coe_am_register_single_taxonomy( $tax = array() ) {
	$_coe         = coe_am_populate_constants();
	$single       = $tax['labels']['singular_name'];
	$name         = $tax['name'];
	$plural       = $tax['labels']['name'];
	$hierarchical = $tax['hierarchical'];
	$description  = stripslashes_deep( $tax['description'] );
	$meta_box_cb  = $tax['meta_box_cb'];

	$labels = array(
		'name'                       => $plural,
		'singular_name'              => $single,
		'search_items'               => sprintf( __( 'Search %s', $_coe['text'] ), $plural ),
		'popular_items'              => sprintf( __( 'Popular %s', $_coe['text'] ), $plural ),
		'all_items'                  => sprintf( __( 'All %s', $_coe['text'] ), $plural ),
		'parent_item'                => sprintf( __( 'Parent %s', $_coe['text'] ), $single ),
		'parent_item_colon'          => sprintf( __( 'Parent %s:', $_coe['text'] ), $single ),
		'edit_item'                  => sprintf( __( 'Edit %s', $_coe['text'] ), $single ),
		'view_item'                  => sprintf( __( 'View %s', $_coe['text'] ), $single ),
		'update_item'                => sprintf( __( 'Update %s', $_coe['text'] ), $single ),
		'add_new_item'               => sprintf( __( 'Add new %s', $_coe['text'] ), strtolower( $single ) ),
		'new_item_name'              => sprintf( __( 'New %s Name', $_coe['text'] ), $single ),
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', $_coe['text'] ), strtolower( $plural ) ),
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', $_coe['text'] ), strtolower( $plural ) ),
		'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', $_coe['text'] ), strtolower( $plural ) ),
		'not_found'                  => sprintf( __( 'No %s found', $_coe['text'] ), strtolower( $plural ) ),
		'no_terms'                   => sprintf( __( 'No %s', $_coe['text'] ), strtolower( $plural ) ),
		'items_list_navigation'      => sprintf( __( '%s list navigation', $_coe['text'] ), $plural ),
		'items_list'                 => sprintf( __( '%s list', $_coe['text'] ), $plural ),
		'most_used'                  => sprintf( __( 'Most Used %s', $_coe['text'] ), $plural ),
		'back_to_items'              => sprintf( __( '← Back to %s', $_coe['text'] ), $plural ),
		'menu_name'                  => $plural,
		'new_item'                   => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		'view_items'                 => sprintf( __( 'View %s', $_coe['text'] ), $plural ),
		'not_found_in_trash'         => sprintf( __( 'No %s found in trash', $_coe['text'] ), strtolower( $plural ) ),
		'archives'                   => sprintf( __( '%s Archives', $_coe['text'] ), $single ),
		'attributes'                 => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		'insert_into_item'           => sprintf( __( '%s Attributes', $_coe['text'] ), $single ),
		'uploaded_to_this_item'      => sprintf( __( 'Uploaded to this %s', $_coe['text'] ), strtolower( $single ) ),
		'archive_title'              => $plural,
		'name_admin_bar'             => $single,
	);

	$args = array(
		'labels'              => $labels,
		'description'         => ( isset( $tax['description'] ) ) ? $tax['description'] : '',
		'public'              => ( isset( $tax['public'] ) ) ? $tax['public'] : true,
		'publicly_queryable'  => ( isset( $tax['publicly_queryable'] ) ) ? $tax['publicly_queryable'] : true,
		'exclude_from_search' => ( isset( $tax['exclude_from_search'] ) ) ? $tax['exclude_from_search'] : false,
		'show_admin_column'   => ( isset( $tax['show_admin_column'] ) ) ? $tax['show_admin_column'] : true,
		'show_ui'             => ( isset( $tax['show_ui'] ) ) ? $tax['show_ui'] : true,
		'show_in_menu'        => ( isset( $tax['show_in_menu'] ) ) ? $tax['show_in_menu'] : true,
		'query_var'           => ( isset( $tax['query_var'] ) ) ? $tax['query_var'] : true,
		'show_in_admin_bar'   => ( isset( $admin ) ) ? $admin : true,
		'capability_type'     => ( isset( $tax['capability_type'] ) ) ? $tax['capability_type'] : 'post',
		'has_archive'         => ( isset( $tax['has_archive'] ) ) ? $tax['has_archive'] : true,
		'hierarchical'        => ( isset( $hierarchical ) ) ? $hierarchical : true,
		'rewrite'             => ( isset( $tax['rewrite'] ) ) ? $tax['rewrite'] : array(
			'slug'         => ( isset( $tax['rewrite']['slug'] ) ) ? $tax['rewrite']['slug'] : $name,
			'with_front'   => ( isset( $tax['rewrite']['with_front'] ) ) ? $tax['rewrite']['with_front'] : true,
			'hierarchical' => ( isset( $tax['rewrite']['hierarchical'] ) ) ? $tax['rewrite']['hierarchical'] : false,
		),
		'supports'            => ( isset( $tax['supports'] ) ) ? $tax['supports'] : array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'revisions',
			'page-attributes',
			'post-formats',
		),
		'show_in_rest'        => ( isset( $tax['show_in_rest'] ) ) ? $tax['show_in_rest'] : true,
		'menu_position'       => ( isset( $tax['menu_position'] ) ) ? $tax['menu_position'] : 21,
		'menu_icon'           => ( isset( $tax['menu_icon'] ) ) ? $tax['menu_icon'] : 'dashicons-admin-generic',
		'show_in_nav_menus'   => ( isset( $tax['show_in_nav_menus'] ) ) ? $tax['show_in_nav_menus'] : true,
		'meta_box_cb'         => $meta_box_cb,
	);

	$object_type = ! empty( $tax['object_types'] ) ? $tax['object_types'] : 'asset';

	/**
	 * Filters the arguments used for a taxonomy right before registering.
	 *
	 * @since 1.0.0
	 * @since 1.3.0 Added original passed in values array
	 * @since 1.6.0 Added $obect_type variable to passed parameters.
	 *
	 * @param array  $args        Array of arguments to use for registering taxonomy.
	 * @param string $value       Taxonomy slug to be registered.
	 * @param array  $taxonomy    Original passed in values for taxonomy.
	 * @param array  $object_type Array of chosen post types for the taxonomy.
	 */
	//$args = apply_filters( 'cptui_pre_register_taxonomy', $args, $tax['name'], $tax, $object_type );

	return register_taxonomy( $tax['name'], $object_type, $args );
}
