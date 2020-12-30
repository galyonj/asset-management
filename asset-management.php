<?php
/**
 * @link              https://github.com/galyonj
 * @since             1.0.3
 * @package           Coe_Am
 *
 * Plugin Name:       Asset Management
 * Plugin URI:        https://github.com/galyonj/coe-asset-management
 * Description:       Create an Assets custom post type and provide tools for managing Assets taxonomy and metadata.
 * Version:           1.0.3
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
/**
 * Define constants to use for our plugin.
 *
 * @param string $constant_name name of constant to be defined.
 * @param string $value         value of constant to be defined.
 *
 * @since 1.0.0
 */
function coe_am_define_constants( string $constant_name, string $value ) {
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
function coe_am_constants() {
	return array(
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
}

/**
 * Put value of plugin constants into an array for easier access
 */
$_coe = coe_am_constants();

function john_screwed_up() {
	delete_option( 'coe_am_metadata' );

	$taxes = get_object_taxonomies( 'asset', 'objects' );

	foreach ( $taxes as $tax ) {
		unregister_taxonomy( $tax->name );
	}
}
//add_action( 'init', 'john_screwed_up' );

/**
 * Register the plugin's text domain
 *
 * @since 1.0.0d
 * @internal
 */
function coe_am_load_textdomain() {
	$_coe = coe_am_constants();

	load_plugin_textdomain( $_coe['text'], '', dirname( $_coe['path'] . 'languages' ) );
}
add_action( 'plugins_loaded', 'coe_am_load_textdomain' );

/**
 * Create the submenu for our plugin and post type
 * on the admin dashboard.
 *
 * @since 1.0.0
 */
function coe_am_create_submenu() {
	$_coe        = coe_am_constants();
	$capability  = apply_filters( 'coe_am_required_caps', 'manage_options' );
	$is_dev      = ( isset( $_SERVER['SERVER_NAME'] ) && '127.0.0.1' === $_SERVER['SERVER_NAME'] );
	$menu_title  = ( $is_dev ) ? 'Manage Metadata' : 'Manage Metadata';
	$menu_slug   = ( $is_dev ) ? 'add-metadata' : 'metadata';
	$parent_path = 'edit.php?post_type=asset';

	if ( $is_dev ) {
		add_submenu_page( $parent_path, __( 'List Metadata', $_coe['text'] ), __( 'Metadata List View', $_coe['text'] ), $capability, 'metadata-list', 'coe_am_metadata_list_html' );
	}
	add_submenu_page( $parent_path, __( $menu_title, $_coe['text'] ), __( 'Manage Metadata', $_coe['text'] ), $capability, 'metadata', 'coe_am_metadata_html' );
}
add_action( 'admin_menu', 'coe_am_create_submenu' );

/**
 * Load required files when the plugin loads
 *
 * @since 1.0.1
 */
function coe_am_required_files() {
	$_coe = coe_am_constants();

	require_once $_coe['path'] . 'classes/class-coe-am-admin-ui.php';
	require_once $_coe['path'] . 'inc/enqueue.php';
	require_once $_coe['path'] . 'inc/utils.php';
	require_once $_coe['path'] . 'inc/register-cpt.php';
	require_once $_coe['path'] . 'inc/metadata-html.php';
	require_once $_coe['path'] . 'inc/metadata-list.php';
	require_once $_coe['path'] . 'inc/process-metadata.php';
	require_once $_coe['path'] . 'inc/metaboxes.php';
	require_once $_coe['path'] . 'inc/deactivation.php';
}
add_action( 'plugins_loaded', 'coe_am_required_files' );

/**
 * Loop through submitted taxonomy information from
 * our option `coe_am_metadata`, and pass each taxonomy
 * to coe_am_register_single_tax.
 *
 * @since 0.5.0
 *
 * @internal
 * @return coe_am_register_single_tax
 */
function coe_am_create_custom_taxes() {
	$taxes = get_option( 'coe_am_metadata' );

	/**
	 * Fires before the start of the taxonomy registrations.
	 *
	 * @param array $taxes Array of taxonomies to register.
	 *
	 * @since 1.0.0
	 */
	do_action( 'coe_am_pre_register_taxes', $taxes );

	if ( ! empty( $taxes ) && is_array( $taxes ) ) {
		foreach ( $taxes as $tax ) {
			/**
			 * Filters whether or not to skip registration of the current iterated taxonomy.
			 *
			 * Dynamic part of the filter name is the chosen taxonomy slug.
			 *
			 * @param bool  $value Whether or not to skip the taxonomy.
			 * @param array $tax   Current taxonomy being registered.
			 *
			 * @since 1.0.1
			 */
			if ( (bool) apply_filters( "coe_am_disable_{$tax['name']}_tax", false, $tax ) ) {
				continue;
			}

			/**
			 * Filters whether or not to skip registration of the current iterated taxonomy.
			 *
			 * @param bool  $value Whether or not to skip the taxonomy.
			 * @param array $tax   Current taxonomy being registered.
			 *
			 * @since 1.0.1
			 */
			if ( (bool) apply_filters( 'coe_am_disable_tax', false, $tax ) ) {
				continue;
			}

			coe_am_register_single_tax( $tax );
		}
	} else {
		return;
	}

	/**
	 * Fires after the completion of the taxonomy registrations.
	 *
	 * @param array $taxes Array of taxonomies registered.
	 *
	 * @since 1.0.0
	 */
	do_action( 'coe_am_post_register_taxes', $taxes );
}
add_action( 'init', 'coe_am_create_custom_taxes', 9 );  // Leave on standard init for legacy purposes.

/**
 * Register metadata when the user submits the form via the manage metadat page
 *
 * @param array $tax array of taxonomy data from get_options( 'coe_am_metadata' ).
 *
 * @return \WP_Error|\WP_Taxonomy
 * @since 1.0.0
 */
function coe_am_register_single_tax( array $tax ) {
	$_coe         = coe_am_constants();
	$single       = $tax['label_singular'];
	$name         = $tax['name'];
	$plural       = $tax['label'];
	$hierarchical = $tax['hierarchical'];

	$labels = array(
		'name'                       => $plural,
		'singular_name'              => $single,
		// translators: string %s is singular name.
		'search_items'               => sprintf( __( 'Search %s', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'popular_items'              => sprintf( __( 'Popular %s', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'all_items'                  => sprintf( __( 'All %s', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'parent_item'                => sprintf( __( 'Parent %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'parent_item_colon'          => sprintf( __( 'Parent %s:', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'edit_item'                  => sprintf( __( 'Edit %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'view_item'                  => sprintf( __( 'View %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'update_item'                => sprintf( __( 'Update %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'add_new_item'               => sprintf( __( 'Add New %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'new_item_name'              => sprintf( __( 'New %s Name', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is plural name.
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is plural name.
		'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is plural name.
		'not_found'                  => sprintf( __( 'No %s found', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is plural name.
		'no_terms'                   => sprintf( __( 'No %s', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is plural name.
		'items_list_navigation'      => sprintf( __( '%s list navigation', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'items_list'                 => sprintf( __( '%s list', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'most_used'                  => sprintf( __( 'Most Used %s', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'back_to_items'              => sprintf( __( '← Back to %s', $_coe['text'] ), $plural ),
		'menu_name'                  => $plural,
		// translators: string %s is plural name.
		'new_item'                   => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		// translators: string %s is plural name.
		'view_items'                 => sprintf( __( 'View %s', $_coe['text'] ), $plural ),
		// translators: string %s is plural name.
		'not_found_in_trash'         => sprintf( __( 'No %s found in trash', $_coe['text'] ), strtolower( $plural ) ),
		// translators: string %s is single name.
		'archives'                   => sprintf( __( '%s Archives', $_coe['text'] ), $single ),
		// translators: string %s is single name.
		'attributes'                 => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		// translators: string %s is single name.
		'insert_into_item'           => sprintf( __( '%s Attributes', $_coe['text'] ), $single ),
		// translators: string %s is single name.
		'uploaded_to_this_item'      => sprintf( __( 'Uploaded to this %s', $_coe['text'] ), strtolower( $single ) ),
		'archive_title'              => $plural,
		'name_admin_bar'             => $single,
	);

	$args = array(
		'name'                => $name,
		'capability_type'     => ( isset( $tax['capability_type'] ) ) ? $tax['capability_type'] : 'post',
		'description'         => ( isset( $tax['description'] ) ) ? $tax['description'] : '',
		'exclude_from_search' => ( isset( $tax['exclude_from_search'] ) ) ? $tax['exclude_from_search'] : false,
		'has_archive'         => ( isset( $tax['has_archive'] ) ) ? $tax['has_archive'] : true,
		'hierarchical'        => ( isset( $tax['hierarchical'] ) ) ? $tax['hierarchical'] : false,
		'labels'              => $labels,
		'menu_icon'           => ( isset( $tax['menu_icon'] ) ) ? $tax['menu_icon'] : 'dashicons-admin-generic',
		'menu_position'       => ( isset( $tax['menu_position'] ) ) ? $tax['menu_position'] : 21,
		'meta_box_cb'         => ( isset( $tax['meta_box_cb'] ) ) ? $tax['meta_box_cb'] : '',
		'public'              => ( isset( $tax['public'] ) ) ? $tax['public'] : true,
		'publicly_queryable'  => ( isset( $tax['publicly_queryable'] ) ) ? $tax['publicly_queryable'] : true,
		'query_var'           => ( isset( $tax['query_var'] ) ) ? $tax['query_var'] : true,
		'rewrite'             => array(
			'slug'         => ( isset( $tax['rewrite_slug'] ) ) ? $tax['rewrite_slug'] : strtolower( $plural ),
			'with_front'   => ( isset( $tax['with_front'] ) ) ? $tax['with_front'] : true,
			'hierarchical' => ( isset( $tax['rewrite_hierarchical'] ) ) ? $tax['rewrite_hierarchical'] : false,
		),
		'show_admin_column'   => ( isset( $tax['show_admin_column'] ) ) ? $tax['show_admin_column'] : true,
		'show_in_admin_bar'   => ( isset( $tax['show_in_admin_bar'] ) ) ? $tax['show_in_admin_bar'] : false,
		'show_in_menu'        => ( isset( $tax['show_in_menu'] ) ) ? $tax['show_in_menu'] : true,
		'show_in_nav_menus'   => ( isset( $tax['show_in_nav_menus'] ) ) ? $tax['show_in_nav_menus'] : true,
		'show_in_rest'        => ( isset( $tax['show_in_rest'] ) ) ? $tax['show_in_rest'] : true,
		'rest_base'           => ( isset( $tax['rest_base'] ) ) ? $tax['rest_base'] : strtolower( $plural ),
		'show_ui'             => ( isset( $tax['show_ui'] ) ) ? $tax['show_ui'] : true,
	);

	$object_type = ! empty( $tax['object_types'] ) ? $tax['object_types'] : 'asset';

	/**
	 * Filters the arguments used for a taxonomy right before registering.
	 *
	 * @param array  $args        Array of arguments to use for registering taxonomy.
	 * @param string $value       Taxonomy slug to be registered.
	 * @param array  $taxonomy    Original passed in values for taxonomy.
	 * @param array  $object_type Array of chosen post types for the taxonomy.
	 *
	 * @since 1.0.0
	 */
	return register_taxonomy( $tax['name'], $object_type, $args );
}

/**
 * Return a formatted notice specific to the action undertaken by the user,
 * and formatted based on the action and the result of that action
 *
 * @param  string $action The type of action undertaken by the user
 * @param  bool $success  Whether the user action was successful or not
 * @param  string $name   The name of the metadata at the heart of the action
 */
function coe_am_admin_notices( $action = '', $success = true, $name = '' ) {
	$_coe        = coe_am_constants();
	$msg_class   = array(
		$success ? 'updated' : 'error',
		'notice',
		'is-dismissable',
	);
	$msg_wrapper = '<div id="message" class="%s"><p>%s</p></div>';

	switch ( $action ) {
		case 'add':
			$msg = ( true === $success ) ? 'Metadata ' . $name . ' creation was successful.' : 'Metadata ' . $name . ' creation has failed.';
			break;
		case 'edit':
			$msg = ( true === $success ) ? 'Metadata ' . $name . ' modification was successful.' : 'Metadata ' . $name . ' modification has failed.';
			break;
		default:
			$msg = ( true === $success ) ? 'Metadata ' . $name . ' deletion was successful.' : 'Metadata ' . $name . ' deletion has failed.';
			break;
	}

	sprintf( $msg_wrapper, implode( ' ', $msg_class ), $msg );

}
add_action( 'admin_notices', 'coe_am_admin_notices' );
