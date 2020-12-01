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
function coe_am_get_taxonomy_data() {
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
 * Return a notice based on conditions.
 *
 * @since 1.0.0
 *
 * @param string $action       The type of action that occurred. Optional. Default empty string.
 * @param string $object_type  Whether it's from a post type or taxonomy. Optional. Default empty string.
 * @param bool   $success      Whether the action succeeded or not. Optional. Default true.
 * @param string $custom       Custom message if necessary. Optional. Default empty string.
 * @return bool|string false on no message, else HTML div with our notice message.
 */
function coe_am_admin_notices( $action = '', $object_type = '', $success = true, $custom = '' ) {

	$class       = array();
	$class[]     = $success ? 'updated' : 'error';
	$class[]     = 'notice is-dismissible';
	$object_type = esc_attr( $object_type );

	$messagewrapstart = '<div id="message" class="' . implode( ' ', $class ) . '"><p>';
	$message          = '';
	$messagewrapend   = '</p></div>';

	if ( 'add' === $action ) {
		if ( $success ) {
			$message .= sprintf( __( '%s has been successfully added', $_coe['text'] ), $object_type );
		} else {
			$message .= sprintf( __( '%s has failed to be added', $_coe['text'] ), $object_type );
		}
	} elseif ( 'update' === $action ) {
		if ( $success ) {
			$message .= sprintf( __( '%s has been successfully updated', $_coe['text'] ), $object_type );
		} else {
			$message .= sprintf( __( '%s has failed to be updated', $_coe['text'] ), $object_type );
		}
	} elseif ( 'delete' === $action ) {
		if ( $success ) {
			$message .= sprintf( __( '%s has been successfully deleted', $_coe['text'] ), $object_type );
		} else {
			$message .= sprintf( __( '%s has failed to be deleted', $_coe['text'] ), $object_type );
		}
	} elseif ( 'import' === $action ) {
		if ( $success ) {
			$message .= sprintf( __( '%s has been successfully imported', $_coe['text'] ), $object_type );
		} else {
			$message .= sprintf( __( '%s has failed to be imported', $_coe['text'] ), $object_type );
		}
	} elseif ( 'error' === $action ) {
		if ( ! empty( $custom ) ) {
			$message = $custom;
		}
	}

	if ( $message ) {

		/**
		 * Filters the custom admin notice for CPTUI.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value            Complete HTML output for notice.
		 * @param string $action           Action whose message is being generated.
		 * @param string $message          The message to be displayed.
		 * @param string $messagewrapstart Beginning wrap HTML.
		 * @param string $messagewrapend   Ending wrap HTML.
		 */
		return apply_filters( 'coe_am_admin_notice', $messagewrapstart . $message . $messagewrapend, $action, $message, $messagewrapstart, $messagewrapend );
	}

	return false;
}
