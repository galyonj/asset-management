<?php
/**
 * Helper and utility functions
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/utils
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Fetch our taxonomy option
 *
 * @since 1.0.0
 *
 * @return mixed
 */
function coe_am_get_saved_metadata() {
	return apply_filters( 'coe_am_get_taxonomy_data', get_option( 'coe_am_metadata', array() ) );
}

/**
 * Flush_rewrite_rules is an expensive operation, and we don't want to perform it any more than we absolutely
 * have to. However...our plugin makes both a custom post type and creates a framework by which we can
 * later perform CRUD operations on taxonomies related to that post type, we're going to have to flush
 * the WordPress rewrite rules.
 *
 * This function looks for a short-lived transient that will be created during the CRUD process for our post type
 * or any of our taxonomies. If that transient is found, we do a soft flush of the rewrite rules upon activation
 * of any of the CRUD operations performed by our plugin.
 *
 * @since 1.0.0
 * @link https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
 * @link https://developer.wordpress.org/reference/functions/get_transient/
 */
function coe_am_flush_rewrite_rules() {

	if ( wp_doing_ajax() ) {
		return;
	}

	/*
	 *
	 */
	if ( 'true' === ( $flush_it = get_transient( 'coe_am_flush_rewrite_rules' ) ) ) {
		flush_rewrite_rules( false );
		// So we only run this once.
		delete_transient( 'coe_am_flush_rewrite_rules' );
	}
}
add_action( 'admin_init', 'coe_am_flush_rewrite_rules' );

/**
 * Check whether or not we're on a new install.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function coe_am_is_new_install() {
	$new_or_not = true;
	$saved      = get_option( 'coe_am_new_install', '' );

	if ( 'false' === $saved ) {
		$new_or_not = false;
	}

	/**
	 * Filters the new install status.
	 *
	 * Offers third parties the ability to override if they choose to.
	 *
	 * @since 1.5.0
	 *
	 * @param bool $new_or_not Whether or not site is a new install.
	 */
	return (bool) apply_filters( 'coe_am_is_new_install', $new_or_not );
}

/**
 *
 * @return void
 */
function coe_am_get_saved_taxes() {
	return apply_filters( 'coe_am_get_saved_taxes', get_option( 'coe_am_metadata', array() ), get_current_blog_id() );
}


/**
 * Construct admin notices for the metadata CRUD processes
 *
 * @param  string $message admin notice message text
 * @param  bool $success whether the user action succeeded or not
 * @return void
 */
function coe_am_admin_notices_helper( string $message, $success = true ) {
	$action      = '';
	$msg_class   = array();
	$msg_class[] = $success ? 'updated' : 'error';
	$msg_class[] = 'notice is-dismissable';
	$msg_start   = '<div id="message" class"' . implode( ' ', $msg_class ) . '"></p>';
	$msg         = '';
	$msg_end     = '</p></div>';

	return apply_filters( 'coe_am_admin_notice', $msg_start, $msg, $msg_end, $action, $msg, $msg_start, $msg_end );
}

/**
 * filter_var shorthand because I'm lazy
 *
 * @since 1.0.3
 * @param $val variable to be converted
 * @return bool
 */
function coerce_bool( $val ) {
	return filter_var( $val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
}
