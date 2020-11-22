<?php
/**
 * Activation functions for the plugin
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/submenu
 * @license GPL-2.0+
 */

defined( 'ABSPATH' ) || die( 'That\'s illegal. ' );

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

	add_submenu_page( $parent_path, __( 'Manage Metadata', $_coe['text'] ), __( 'Manage Metadata', $_coe['text'] ), $capability, 'metadata', 'coe_am_metadata' );
	//add_submenu_page( $parent_path, __( 'Help', $_coe['text'] ), __( 'Help', $_coe['text'] ), $capability, 'help', 'display_help' );
}
add_action( 'admin_menu', 'coe_am_create_submenu' );
