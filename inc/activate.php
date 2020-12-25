<?php
/**
 * Activation scripts for our plugin
 *
 * @author     John Galyon
 * @since      1.0.1
 * @package    Coe_Am
 * @subpackage Coe_Am/activate
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * On plugin activation, we want to set a short transient
 * so that we can listen to it later.
 *
 * @author John Galyon
 * @since  1.0.0
 * @uses   \set_transient()
 */
function coe_am_activation_transient() {
	/**
	 * Stop if it's a network or bulk upload
	 */
	if ( is_network_admin() ) {
		return;
	}
	set_transient( 'coe_am_activation_redirect', true, 30 );
}
add_action( 'activate_' . plugin_basename( __FILE__ ), 'coe_am_activation_transient' );

/**
 * With the transient set in coe_am_activation_transient,
 * we can now listen for it and redirect the user to our metadata page
 * on plugin activation
 *
 * @author John Galyon
 * @since  1.0.0
 * @uses
 */
function coe_am_activation() {

	/**
	 * If the transient doesn't exist, stop.
	 */
	if ( ! get_transient( 'coe_am_activation_redirect' ) ) {
		return;
	}

	/**
	 * Having found the transient, we want to make sure that we delete it
	 * so that it's not in memory to cause mischief later. We also want
	 * to flush the rewrite rules just to make sure we're working from a
	 * clean slate.
	 *
	 * @since 1.0.0
	 * @uses \delete_transient()
	 * @uses \flush_rewrite_rules()
	 */
	delete_transient( 'coe_am_activation_redirect' );
	flush_rewrite_rules( false );

	/**
	 * Once the transient has been found and deleted, if we're
	 * on the network admin screen, or this isn't a new installation,
	 * stop.
	 */
	if ( is_network_admin() || ! coe_am_new_install() ) {
		return;
	}

	/**
	 * Having deleted the transient and flushed our rewrite rules, redirect the user to the metadata page
	 *
	 * @since 1.0.0
	 * @uses  \wp_safe_redirect()
	 * @uses
	 */
	wp_safe_redirect(
		add_query_arg(
			array(
				'post_type' => 'asset',
				'page'      => 'metadata',
				'action'    => 'add',
			),
			admin_url( 'edit.php' )
		)
	);

}
add_action( 'init', 'coe_am_activation', 1 );
